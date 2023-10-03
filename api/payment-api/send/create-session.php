<?php defined('ABSPATH') or die();

/**
 * Create a new session in FastSpring
 * @param array $discount
 * $discount = array( 'items' => array( 'product_path', 'discount_type', 'discount' ), 'discount' => array( 'discount_type', 'discount' ) )
 */
function mwfi_create_session( $coupon = null )
{
    $loggedIn = false;
    //Check if customer is logged in
    if ( is_user_logged_in() )
    {
        $loggedIn = true;
    }
    //get woo commerce cart
    $cart = WC()->cart->get_cart();

    //Get items from cart and subsequent fastspring id (Stored in product meta)
    $cart_items = array();
    foreach ( $cart as $item_key => $item ) {
        //Get products slug
        $product = $item['data'];
        $cart_items[] = array(
            'product' => $product->get_slug(),
            'quantity' => $item['quantity'],
        );
    }

    //Get customer data
    //Check if customer has WC data
    $customer = WC()->customer;
    //Check if cart has negative fees

    //Create a session array
    $session_array = array(
        'contact' => array(
            "first" => $customer->get_first_name(),
            "last" => $customer->get_last_name(),
            "email" => $customer->get_email(),
            "country" => $customer->get_billing_country(),
        ),
        'language' => 'en',
        'tags' => array(
            'wp_id_' => get_current_user_id()
        ),
        'items' => $cart_items,
    );
    if ( $coupon != null )
    {
        //TODO: Add precheck for coupon for validity
        $session_array['coupon'] = $coupon;
    }
    $headers = mwfi_create_headers();
    //wp_die( var_dump( $headers ) );
    $request = mwfi_curl_request(
        'https://api.fastspring.com/sessions',
        'POST',
        $session_array,
        $headers,
    );

    //Check if request was successful returns response + httpCode
    if ( $request['httpCode'] == 200 )
    {
        //JSON decode response
        $request['response'] = json_decode($request['response'], true);
        //Get session id
        $session_id = $request['response']['id'];
        $session_id = sanitize_text_field( $session_id );
        //Return session id
        return array(
            'session_id' => $session_id,
        );
    }
    else
    {
        //Return error
        return array(
            'error' => $request['httpCode'] . ' ' . $request['response'],
        );
    }
}

?>