<?php 
ini_set('display_errors', 1); error_reporting(E_ALL);
/**
 * Plugin Name: Mythic Woocommerce Fastspring Integration
 * Plugin URI:
 * Description: This plugin integrates WooCommerce with Fastspring to allow for the purchase of digital products and services through Fastspring. While still using WooCommerce for the rest of your store for admin purposes.
 * Version: 1.0.0
 * Author: Hayden Falzon
 */

/**
 * Reason for this plugin:
 * WooCommerce is a great plugin for managing a store, but it is not designed for digital products. It is designed for physical products.
 * It also does not have a built in way to handle subscriptions.
 * And it does not have a built in way to handle tax remittance.
 * That said, it is still a great plugin for managing a store. And should FastSpring ever go down, you can still use WooCommerce to manage your store as it is local.
 */
// OPGC5RLBR1KXO6QPGHZIKW - Test Credential
// sOE3Yyx-S-quMJdUvQuulw - Test Password

defined( 'ABSPATH' ) or die();

define ( 'MWFI_PATH', plugin_dir_path( __FILE__ ) ); //Path to the plugin directory
//Load the functions file
require MWFI_PATH . 'functions.php';
//Load the database file
require MWFI_PATH . 'database/databases.php';
register_activation_hook( __FILE__, 'mwfi_create_database_table'); //Create the database table on plugin activation

//Load the settings page
require MWFI_PATH . 'admin/pages/settings.php';

//Check if wooCommerce is active
function mwfi_is_woocommerce_active()
{
    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
    {
        return true;
    }
    else
    {
        return false;
    }
}


if ( !mwfi_is_woocommerce_active() )
{
    return;
}

//Load the custom columns file
require MWFI_PATH . 'admin/custom-columns/product-columns.php';
require MWFI_PATH . 'admin/metaboxes/products-metabox.php';

//Load the Fastspring API file
require MWFI_PATH . 'api/fastspring-api.php';




?>