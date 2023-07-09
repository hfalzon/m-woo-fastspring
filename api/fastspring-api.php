<?php 
defined('ABSPATH') or die(); 

/**
 * Create a curl request
 * @param string $url
 * @param string $method (GET, POST, PUT, DELETE, FETCH)
 * @param array $data
 * @param array $headers
 * @param int $timeout
 * @param bool $ssl
 * @param bool $debug
 * @return array $response (response, httpCode)
 */
function mwfi_curl_request($url, $method, $data = array(), $headers = array(), $timeout = 10, $ssl = true, $debug = false )
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout+10 );
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl);
    //Data must be set and must be using a method that requires data
    if ( ( $method === 'POST' && !empty($data) ) || ( $method === 'PUT' && !empty($data) ) ) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        array_push($headers, 'Content-Type: application/json');
    }
    //Headers must be set
    if ( !empty($headers) ) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    //Debug
    //wp_die( var_dump( $headers ) );
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    return array(
        'response' => $response,
        'httpCode' => $httpCode
    );
}

//Get the admin api files
//Sends
require MWFI_PATH . 'api/admin-api/send/create-update-product.php';
//Gets
require MWFI_PATH . 'api/admin-api/get/get-product.php';
require MWFI_PATH . 'api/admin-api/get/get-products.php';
require MWFI_PATH . 'api/admin-api/get/get-subscriptions.php';

//Deletes
require MWFI_PATH . 'api/admin-api/delete/delete-product.php';

//Get the store api files
//Sends
require MWFI_PATH . 'api/payment-api/send/create-session.php';
require MWFI_PATH . 'api/payment-api/send/return-sub.php'; //Refund
require MWFI_PATH . 'api/payment-api/send/resume-cancelled-subscription.php'; //Resume Cancelled Subscription

//Gets

//Deletes
require MWFI_PATH . 'api/payment-api/delete/cancel-subscription.php'; //Cancel Subscription

?>