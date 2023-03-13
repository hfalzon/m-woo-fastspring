<?php defined('ABSPATH') or die();

//on delete product
add_action('woocommerce_before_delete_product', 'mwfi_delete_product');
function mwfi_delete_product( $product_id )
{
    //Get product and path link from db
    global $wpdb;
    $table_name = $wpdb->prefix . 'mwfi_products';
    $query = "SELECT fs_product_path FROM $table_name WHERE wc_product_id = '%d'";
    $product = $wpdb->get_var( $wpdb -> prepare( $query, $product_id ) );

    //Check if product retuns a value
    if (!$product)
    {
        return;
    }

    //Get Fastspring API credentials
    $fs_username = get_option('mwfi_api_key');
    $fs_password = mwfi_decode( get_option('mwfi_api_secret_key') );

    //create Auth token
    $auth = base64_encode($fs_username . ":" . $fs_password);

    //Get url to product
    $fs_product_url = 'https://api.fastspring.com/products/'.$product;

    //Delete product from Fastspring
    $curl_options = array(
        CURLOPT_URL => $fs_product_url,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Basic " . $auth
        ],
        CURLOPT_RETURNTRANSFER => true,
    );
    $curl = curl_init();

    curl_setopt_array($curl, $curl_options);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);

    //Error handling
    if ($err) {
        //echo "cURL Error #:" . $err;
        wp_redirect( admin_url( 'post.php?post=' . $product_id . '&action=edit&message=1' ) );
        exit;
        //wp_die( 'Error: ' . $err);
    }
    //Success Check
    if($http_code == 200 || $http_code == 201)
    {
        //Delete product from db
        $wpdb->delete( $table_name, array( 'wc_product_id' => $product_id ) );
    }
    else
    {
        //Display error
        wp_redirect( admin_url( 'post.php?post=' . $product_id . '&action=edit&message=1' ) );
        exit;
        //wp_die( 'Error: ' . $response );
    }
}
?>