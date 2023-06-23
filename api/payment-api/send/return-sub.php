<?php defined('ABSPATH') or die();

function mwfi_refund_subscription( string $path )
{
    if ( !is_user_logged_in() ) return false;
    //Check if user is subscribed
    $user_id = get_current_user_id();
    //Check path is a valid string with only letters, numbers, dashes and underscores
    if ( !preg_match('/^[a-zA-Z0-9-_]+$/', $path) ) return false;
    //Get subscription id
    global $wpdb;
    $subscription = $wpdb -> get_row( $wpdb -> prepare( "SELECT * FROM {$wpdb -> prefix}mwfi_subscriptions WHERE user_id = '%d' AND subscription_status = 1 AND fs_product_path = '%s'", $user_id, $path) );
    if ( ! $subscription ) return false;
    //Check if subscription is active
    if ( $subscription -> subscription_status != true ) return false;
    if ( $subscription -> fs_order_id == null )
    {
        //Send email to admin to notify them of the error
        $admin_email = get_option('admin_email');
        $subject = 'Subscription Error';
        $message = 'Subscription Error: Subscription is active but no order id is set for user_id' . absint( $user_id ) . '';
        wp_mail($admin_email, $subject, $message);
        return false;
    }

    //Create Return Request and Cancel Subscription request - (As returning does not cancel the subscription)
    $headers = mwfi_create_headers();

    $return_request = mwfi_curl_request(
        'https://api.fastspring.com/returns',
        'POST',
        array(
            'returns' => array(
                array(
                    'order' => $subscription -> fs_order_id, //Need to get order id currenty only have sub_id
                    'reason' => '15 day money back guarantee',
                    'notification' => 'ORGINAL'
                )
            )
        ),
        $headers
    );
    //Check if return request was successful
    if ( $return_request['httpCode'] != 200 ) return false;

    $cancel_request = mwfi_curl_request(
        'https://api.fastspring.com/subscriptions/' . $subscription -> fs_subscription_id,
        'DELETE',
        array(),
        $headers
    );

    //Check if cancel request was successful
    if ( $cancel_request['httpCode'] != 200 ) return false;

    //Update subscription status
    $update = $wpdb -> update(
        $wpdb -> prefix . 'mwfi_subscriptions',
        array(
            'subscription_status' => false,
            'subscription_next_payment' => null,
            'subscription_end' => current_time('mysql')
        ),
        array('user_id' => $user_id),
        array(
            '%d',
            '%s',
            '%s'
        ),
        array('%d')
    );

    if ( $update === false ) return false;

    return true;
}