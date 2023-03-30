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
        fs_taxcode varchar(25) NOT NULL,
        fs_product_type tinyint(4) NOT NULL,
        wc_product_id bigint(20) unsigned NOT NULL,
        fs_product_subscription tinyint(1) unsigned NOT NULL DEFAULT 0,
        fs_product_subscription_interval varchar(25) default NULL,
        fs_product_subscription_interval_length tinyint(4) default NULL,
        PRIMARY KEY (id),
        KEY (fs_product_path),
        KEY (wc_product_id),
        KEY (fs_taxcode),
        KEY (fs_product_type),
        KEY (fs_product_subscription),
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