<?php defined('ABSPATH') or die();

//Register a webhook to listen for order complete events
add_action('rest_api_init', 'mwfi_register_order_complete_endpoint');
function mwfi_register_order_complete_endpoint() {
    register_rest_route('mwfi/v1', '/order-complete', array(
        'methods' => 'POST',
        'callback' => 'mwfi_order_complete_handle_endpoint',
        'permission_callback' => '__return_true'
    ));
}

//Handle the webhook request
function mwfi_order_complete_handle_endpoint( WP_REST_Request $request )
{
    //Check if the header has a X-FS-Signature
    if ( !isset($_SERVER['X-Fs-Signature']) )
    {
        return new WP_REST_Response(array('success' => false, 'error' => 'No signature'), 400); //Bad request
    }
    //Validate the signature
    $signature = $_SERVER['X-Fs-Signature'];
    $hash = hash_hmac('sha256', file_get_contents('php://input'), '15sqAsldkqqQ8SLDa', true); //TODO - Move to settings
    if ( $signature != $hash )
    {
        return new WP_REST_Response(array('success' => false, 'error' => 'Invalid signature'), 400); //Bad request
    }
    //Check if the request is valid

    //Get the order reference
    $payload = json_decode($request->get_body(), true);
    $order_id = $payload['events'][0]['data']['order'];
    //Get the customer
    $fs_wp_user = $payload['events'][0]['data']['tags']['wp_id_'];
    $fs_wp_email = $payload['events'][0]['data']['customer']['email'];
    $products = $payload['events'][0]['data']['items'];
    //Check in the products array if there is a subscription
    $fs_is_subscription = false;
    $fs_is_lifetime_subscription = false;
    foreach ( $products as $product )
    {
        if ( isset($product['isSubscription']) && $product['isSubscription'] == true )
        {
            $fs_is_subscription = true;
            $fs_subscription_product = $product['product'];
            $fs_subscription_id = $product['subscription'];
            break;
        }
        //Check for lifetime access product
        if ( isset($product['product']) && $product['product'] == 'lifetime-memebership' ) //Hardcoded for now
        {
            $fs_is_lifetime_subscription = true;
            $fs_subscription_product = $product['product'];
            $fs_lifetime_order_id = $payload['events'][0]['data']['order']; //Should not be able to put lifetime Subs into cart with other items. -> Important for refund purposes.
            break;
        }
    }
    $fs_order_type = $payload['events'][0]['type'];
    $valid = false;
    //DEBUG RESPONSE
    //wp_die( var_dump( mwfi_valid_order_id($order_id), $order_id ) ); //Debug
    //Validate data
    $valid_order_types = array('order.completed', 'order.payment.pending', 'order.canceled', 'order.failed' );
    if ( mwfi_valid_order_id($order_id) && is_numeric($fs_wp_user) && is_email($fs_wp_email) && is_bool($fs_is_subscription) && in_array($fs_order_type, $valid_order_types) )
    {
        $valid = true;
    }
    //If data is invalid
    if ( !$valid )
    {
        return new WP_REST_Response(array('success' => false, 'error' => 'Invalid data', 'is_numeric' => $fs_order_type), 400); //Bad request
    }

    //DEBUG RESPONSE
    //return new WP_REST_Response(array('success' => true, 'wp_id' => absint($fs_wp_user), 'email' => $fs_wp_email), 200); //Debug

    //Then we need to check if the user exists in WordPress
    $wp_user = get_user_by('id', $fs_wp_user);
    if ( !$wp_user )
    {
        return new WP_REST_Response(array('success' => false, 'error' => 'User does not exist in WordPress'), 400); //Bad request
    }
    //Check if the users email matches the email in FastSpring
    if ( $wp_user -> user_email != $fs_wp_email )
    {
        //If the email does not match
        //Then we need to check if the email exists in WordPress
        $wp_user_check = get_user_by('email', $fs_wp_email);
    }

    //If the order is a subscription order - set database values
    if ( $fs_is_subscription === true )
    {
        if ( !mwfi_valid_order_id($fs_subscription_id) )
        {
            return new WP_REST_Response(array('success' => false, 'error' => 'Invalid subscription ID'), 400); //Bad request
        }
        $request_url = 'https://api.fastspring.com/subscriptions/' . $fs_subscription_id;
        //Get the subscription data
        $fs_subscription = mwfi_curl_request( $request_url, 'GET', array(), mwfi_create_headers() );
        $json = json_decode($fs_subscription['response'], true);

        //Check if the subscription is valid
        if ( $fs_subscription['httpCode'] != 200 )
        {
            return new WP_REST_Response(array('success' => false, 'error' => 'Invalid subscription ID'), 400); //Bad request
        }
        //Check if the subscription is active

        $active = $json['active'];
        $next_period_date = $json['nextChargeDateDisplay']; //Shows as 19/07/23 being dd/mm/yy
        //Convert to datetime
        $next_period_date = DateTime::createFromFormat('d/m/y', $next_period_date);
        $next_period_date = $next_period_date->format('Y-m-d H:i:s');
        //Get the start date
        $start_date = $json['beginDisplay'];
        //Convert to datetime
        $start_date = DateTime::createFromFormat('d/m/y', $start_date);
        $start_date = $start_date->format('Y-m-d H:i:s');
        //Get the end date
        $end_date = null;
        if ( $json['end'] != null)
        {
            $end_date = $json['endDisplay'];
            //Convert to datetime
            $end_date = DateTime::createFromFormat('d/m/y', $end_date);
            $end_date = $end_date->format('Y-m-d H:i:s');
        }
        //Get Next Payment Cost
        if ( !is_bool($active) ) return new WP_REST_Response(array('success' => false, 'error' => 'Invalid subscription data'), 400); //Bad request


        //Store the data in the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'mwfi_subscriptions';

        //Check if the subscription already exists
        $subscription_exists = $wpdb -> get_row( $wpdb -> prepare("SELECT * FROM $table_name WHERE fs_subscription_id = %s AND user_id = %d", $fs_subscription_id, $wp_user -> ID) );
        if ( isset($subscription_exists) )
        {
            //For now we will just return a success response
            return new WP_REST_Response(array('success' => false, 'sub_id' => $fs_subscription_id, 'error' => 'Subscription already exists'), 200); //Bad request
        }
        //wp_die($fs_subscription_product);
        $wpdb -> insert(
            $table_name,
            array(
                'user_id' => $wp_user -> ID,
                'fs_subscription_id' => $fs_subscription_id,
                'fs_order_id' => $order_id,
                'subscription_next_payment' => $next_period_date,
                'subscription_status' => ($active != true)? false : true,
                'fs_product_path' => $fs_subscription_product,
                'subscription_start' => $start_date,
                'subscription_end' => $end_date,
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
            )
        );
    }
    elseif ( $fs_is_lifetime_subscription === true )
    {
        //Store the data in the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'mwfi_subscriptions';

        //Check if the subscription already exists
        $subscription_exists = $wpdb -> get_row( $wpdb -> prepare("SELECT * FROM $table_name WHERE fs_order_id = %s AND user_id = %d", $fs_lifetime_order_id, $wp_user -> ID) );
        if ( isset($subscription_exists) )
        {
            //For now we will just return a success response
            return new WP_REST_Response(array('success' => false, 'sub_id' => $fs_subscription_id, 'error' => 'Subscription already exists'), 200); //Bad request
        }
        //wp_die($fs_subscription_product);
        $wpdb -> insert(
            $table_name,
            array(
                'user_id' => $wp_user -> ID,
                'fs_subscription_id' => $fs_lifetime_order_id,
                'fs_order_id' => $fs_lifetime_order_id,
                'subscription_next_payment' => null,
                'subscription_status' => true,
                'fs_product_path' => $fs_subscription_product,
                'subscription_start' => date('Y-m-d H:i:s'),
                'subscription_end' => null,
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
            )
        );
    }
    //TODO - Deal with basic orders
    //do action - 
    do_action('mwfi_first_order_complete', $fs_is_subscription, $wp_user -> ID );
    
    //Return success
    return new WP_REST_Response(array('success' => true), 200);
    //Returning success before the order is create in WooCommerce

    //Create a new order in WooCommerce
    $order = wc_create_order();
    $order->set_address($payload['customer'], 'billing');
    $order->set_address($payload['customer'], 'shipping');
    $order->set_customer_id($payload['customer']['email']);
    $order->set_payment_method('fastspring');
    $order->set_total($payload['total']);
    $order->set_currency($payload['currency']);

    //Send the order complete event
    mwfi_sse_order_complete(5, 16);

    //Return success
    return new WP_REST_Response(array('success' => true), 200);
}

function mwfi_valid_order_id( $order )
{
    //Check if the order is valid make up of letters, numbers, dashes and underscores and between 5 - 25 characters
    if ( preg_match('/^[a-zA-Z0-9-_]{5,25}$/', $order) )
    {
        return true;
    }
    return false;
}