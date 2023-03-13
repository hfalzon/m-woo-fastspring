<?php
defined('ABSPATH') or die(); 

//Create database table for storing Fastspring product IDs and WooCommerce product IDs
function mwfi_create_database_table()
{
    global $mwfi_db_version;

    global $wpdb;
    $table_name = $wpdb->prefix . 'mwfi_products';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        fs_product_path varchar(255) NOT NULL,
        wc_product_id bigint(20) unsigned NOT NULL,
        PRIMARY KEY (id),
        KEY (fs_product_path),
        KEY (wc_product_id),
        FOREIGN KEY (wc_product_id) REFERENCES {$wpdb -> prefix}posts(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    if ( $wpdb -> last_error )
    {
        //Create a debug log
        error_log( $wpdb -> last_error );
    }
}

//Add the database on plugin activation


?>