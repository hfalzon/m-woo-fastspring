<?php 

function mwfi_create_session()
{
    //get woo commerce cart
    $cart = WC()->cart->get_cart();

    //Get items from cart and subsequent fastspring id (Stored in product meta)
    $items = array();
    foreach($cart as $item => $values) { 
        $_product =  wc_get_product( $values['data']->get_id()); 
        $items[] = array(
            'product' => $_product->get_id(),
            'name' => $_product->get_name(),
            'quantity' => $values['quantity'],
            'fastspring_id' => get_post_meta($_product->get_id(), 'fastspring_id', true)
        );
    }

    //Get customer details
    $customer = array(
        'email' => WC()->customer->get_email(),
        'firstName' => WC()->customer->get_first_name(),
        'lastName' => WC()->customer->get_last_name(),
        'address' => WC()->customer->get_billing_address_1(),
        'address2' => WC()->customer->get_billing_address_2(),
        'city' => WC()->customer->get_billing_city(),
        'state' => WC()->customer->get_billing_state(),
        'zip' => WC()->customer->get_billing_postcode(),
        'country' => WC()->customer->get_billing_country(),
        'phone' => WC()->customer->get_billing_phone()
    );

    //Create a new order in FastSpring and get the checkout link
    $checkout_link = mwfi_create_order($items, $customer);

    //return the checkout link
    return $checkout_link;
}

function mwfi_create_order($items, $customer)
{
    $order_data = array(
        'items' => [],
        'currency' => 'USD',
        'returnURL' => 'http://localhost:10047/'
        'test' => true
    );

    //Add items to order
    foreach($items as $item)
    {
        $order_data['items'][] = array(
            'product' => $item['fastspring_id'],
            'quantity' => $item['quantity']
        );
    }
    //Check if customer has details like address, phone number, etc.
    if($customer['address'] != '' && $customer['city'] != '' && $customer['state'] != '' && $customer['zip'] != '' && $customer['country'] != '')
    {
        //Add customer details to order
        $order_data['customer'] = array(
            'email' => $customer['email'],
            'firstName' => $customer['firstName'],
            'lastName' => $customer['lastName'],
            'address' => $customer['address'],
            'address2' => $customer['address2'],
            'city' => $customer['city'],
            'state' => $customer['state'],
            'zip' => $customer['zip'],
            'country' => $customer['country'],
            'phone' => $customer['phone']
        );
    }


}

?>