<?php defined('ABSPATH') or die();

add_action('woocommerce_new_product', 'mwfi_create_update_product', 10, 2); // Apparently fires when you save a product as a draft
add_action('woocommerce_update_product', 'mwfi_create_update_product', 10, 2);
function mwfi_create_update_product( $id, $product )
{
    //wp_die( var_dump( $_POST ) ); //Debug
    /**Testing */
    $username = get_option('mwfi_api_key');
    $password = mwfi_decrypt( get_option('mwfi_api_secret_key') );
    //Get product
    //$product = wc_get_product($post_id);
    //Validate taxcode
    if ( ! preg_match( '/^[a-zA-Z0-9-]+$/', $_POST['mwfi_product_taxcode'] ) ) {
        return;
    }
    $taxcode = $_POST['mwfi_product_taxcode'];
    $format = new mwfi_product_data( $_POST['mwfi_product_type'] );
    //Create pricing array
    $pricing = array(
        'quantityBehavior' => 'allow',
        'quantityDefault' => 1,
        'price' => array(
            get_woocommerce_currency() => $product -> get_price()
        ),
    );
    //Add subscription period if subscription to pricing
    if ( $_POST['mwfi_product_subscription'] == 1 )
    {
        $accpeted_intervals = array('day', 'week', 'month', 'year');
        //Validate interval
        if ( in_array( $_POST['mwfi_product_subscription_interval'], $accpeted_intervals ) ) 
        {
            $pricing['interval'] = sanitize_text_field( $_POST['mwfi_product_subscription_interval'] );
            $pricing['intervalLength'] = absint( $_POST['mwfi_product_subscription_length'] );
        }
    }
    //Create product
    $data = array( 
        'products' => array(
            'product' => $product -> get_slug(),
            'display' => array( 
                'en' => $product -> get_name()
            ),
            'description' => array(
                'summary' => array(
                    'en' => $product -> get_short_description()
                ),
                'full' => array(
                    'en' => $product -> get_description()
                )
            ),
            'format' => $format -> format,
            'taxcode' => $taxcode,
            'pricing' => $pricing,
        )
    );
    //wp_die(var_dump($format));
    //Create a base64 encoded string of the $username and $password
    $auth = base64_encode($username . ':' . $password);
    //Format data
    $product_json = json_encode($data);
    //Create CURL request
    $curl_options = array(
        CURLOPT_URL => 'https://api.fastspring.com/products',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            'authorization: Basic ' . $auth,
            "content-type: application/json"
          ],
        CURLOPT_POSTFIELDS => $product_json,
        CURLOPT_RETURNTRANSFER => true,
    );
    $curl = curl_init();
    curl_setopt_array($curl, $curl_options);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    // get the HTTP response code
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($err) {
        //echo "cURL Error #:" . $err;
        wp_die( 'Connection Error: ' . $err);
    }
    //Check if product was created
    if($http_code == 200 || $http_code == 201)
    {
        //Get product ID
        $product_id = json_decode($response, true)['products'][0]['product'];

        //wp_die( var_dump(json_decode($response, true)) ); //Debug

        //Check if product already exists in db
        global $wpdb;
        $table_name = $wpdb->prefix . 'mwfi_products';
        $query = "SELECT fs_product_path FROM $table_name WHERE wc_product_id = '%d'";
        $query = $wpdb->prepare($query, $id);
        $product_path = $wpdb->get_var($query);
        //Create base array
        $update = array(
            'fs_taxcode' => sanitize_text_field( $taxcode ),
            'fs_product_type' => $format -> id,
        );
        $update_format = array(
            '%s',
            '%d',
        );
        //Check if product is subscription
        if ( $_POST['mwfi_product_subscription'] == 1 ) //TODO LOGIC
        {
            //Add subscription to update array
            $sub_array = array(
                'fs_product_subscription' => 1,
                'fs_product_subscription_interval' => sanitize_text_field( $_POST['mwfi_product_subscription_interval'] ),
                'fs_product_subscription_interval_length' => absint( $_POST['mwfi_product_subscription_length'] ),
            );
            $update = array_merge($update, $sub_array);
            array_push($update_format, '%d', '%s', '%d');
        }
        else
        {
            //Add subscription to update array
            $update['fs_subscription'] = 0;
            array_push($update_format, '%d');
        }
        //If path exists check if it needs to be updated
        if ($product_path)
        {
            //Needs to be updated
            $where = array(
                'wc_product_id' => $id,
            );
            $where_format = array(
                '%d',
            );
            //Check if product path needs to be updated
            if ($product_path != $product_id)
            {
                //Add product path to update array
                $update['fs_product_path'] = $product_id;
                array_push($update_format, '%s');
            }
            //Update product
            $wpdb -> update(
                $table_name,
                $update,
                $where,
                $update_format,
                $where_format
            );
        }
        else
        {
            //Add product ID to custom db table
            $wpdb->insert(
                $table_name,
                array(
                    'wc_product_id' => $id,
                    'fs_product_path' => $product_id,
                    'fs_taxcode' => $taxcode,
                    'fs_product_type' => $format -> id
                ),
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%d'
                )
            );
        }
        return true;
    }
    else
    {
        //Display error
        wp_die( 'Error: ' . $response );
    }
}

?>