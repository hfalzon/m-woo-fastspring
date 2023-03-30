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
    //Get the order reference
    $payload = json_decode($request->get_body(), true);
    $order_id = $payload['orderReference'];
    //Get the customer
    $fs_wp_user = $payload['tags']['wp_id'];
    $fs_wp_email = $payload['customer']['email'];

    //Then we need to check if the user exists in WordPress
    $wp_user = get_user_by('id', $fs_wp_user);
    //Check if the users email matches the email in FastSpring
    if ( $wp_user -> user_email != $fs_wp_email )
    {
        //If the email does not match
        //Then we need to check if the email exists in WordPress
        $wp_user_check = get_user_by('email', $fs_wp_email);

    }

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