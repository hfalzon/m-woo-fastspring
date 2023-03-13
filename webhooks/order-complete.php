<?php defined('ABSPATH') or die();

//Register a webhook to listen for order complete events
add_action('rest_api_init', 'mwfi_register_order_complete_endpoint');
function mwfi_register_order_complete_endpoint() {
    register_rest_route('mwfi/v1', '/order-complete', array(
        'methods' => 'POST',
        'callback' => 'mwfi_order_complete_handle_endpoint',
    ));
}

//Handle the webhook request
function mwfi_order_complete_handle_endpoint( WP_REST_Request $request )
{
    $payload = json_decode($request->get_body(), true);
    $order_id = $payload['orderReference'];

    //Create a new order in WooCommerce
    $order = wc_create_order();
    $order->set_address($payload['customer'], 'billing');
    $order->set_address($payload['customer'], 'shipping');
    $order->set_customer_id($payload['customer']['email']);
    $order->set_payment_method('fastspring');
    $order->set_total($payload['total']);
    $order->set_currency($payload['currency']);

    //Return success
    return new WP_REST_Response(array('success' => true), 200);
}