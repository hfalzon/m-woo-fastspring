<?php defined('ABSPATH') or die();

//Intercept the save products action and save custom data to custom table

add_action( 'save_post_products', 'mwfi_save_products', 10, 3 );

function mwfi_save_products($product_id, $post, $update )
{
    if ( ! $update ) {
        return;
    }
    //Check if user has permissions
    if ( ! current_user_can( 'edit_post', $product_id ) ) {
        return;
    }

    //Validate the data
    if ( ! isset( $_POST['mwfi_product_taxcode'] ) || ! isset( $_POST['mwfi_product_type'] ) ) {
        return;
    }

    //Check if product type is a number
    if ( ! is_numeric( $_POST['mwfi_product_type'] ) ) {
        return;
    }
    
    //Check tax code against Regex only Letters dashes and numbers
    if ( ! preg_match( '/^[a-zA-Z0-9-]+$/', $_POST['mwfi_product_taxcode'] ) ) {
        return;
    }

    //Check if data exists in custom tabe
    global $wpdb;
    $table_name = $wpdb->prefix . 'mwfi_products';
    $product = $wpdb->get_row( "SELECT * FROM $table_name WHERE wc_product_id = $product_id" );

    //If data exists update it
    if ( $product ) {
        $wpdb->update(
            $table_name,
            array(
                'fs_taxcode' => $_POST['mwfi_product_taxcode'],
                'fs_product_type' => $_POST['mwfi_product_type']
            ),
            array( 'wc_product_id' => $product_id ),
            array(
                '%s',
                '%d'
            ),
            array( '%d' )
        );
    } else {
        //If data does not exist insert it
        $wpdb->insert(
            $table_name,
            array(
                'fs_taxcode' => $_POST['mwfi_product_taxcode'],
                'fs_product_type' => $_POST['mwfi_product_type']
            ),
            array(
                '%s',
                '%d'
            )
        );
    }
}

?>