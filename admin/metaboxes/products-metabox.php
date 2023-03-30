<?php defined('ABSPATH') or die(); 

//Create metaboxes for the products
function mwfi_create_product_metaboxes()
{
    add_meta_box(
        'mwfi_product_metabox',
        'Fastspring Product',
        'mwfi_product_metabox_callback',
        'product',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'mwfi_create_product_metaboxes');

//Create the metabox callback
function mwfi_product_metabox_callback($post)
{
    //Get data from database
    global $wpdb;
    $table_name = $wpdb->prefix . 'mwfi_products';
    $product = $wpdb->get_row( $wpdb -> prepare("SELECT * FROM $table_name WHERE wc_product_id = '%d'", $post->ID), ARRAY_A );
    //Create a table for storing the tax code
    //And product type
    ?>
        <style>
            .mwf_div_table{
                display:flex;
                flex-flow: wrap;
            }
            .mwf_div_table > div:nth-child(-n + 6)
            {
                background-color: '#eee';
            }
            .mwf_div_table_item
            {
                width: calc( ( 100% - (31px * 6) )/ 6);
            }
        </style>
        <table class = "form-table">
            <tr valign="top">
                <th scope="row">Fastspring Tax Code</th>
                <td><input type="text" name="mwfi_product_taxcode" value="<?php echo ( isset( $product['fs_taxcode'] ) ) ? esc_html( $product['fs_taxcode'] ) : null ?>" size = "25"/></td>
            </tr>
            <tr valign="top">
                <th scope="row">Fastspring Product Type</th>
                <td>
                    <?php $types = ( new mwfi_product_data( get_the_ID() ) ) -> get_types(); ?>
                    <select name="mwfi_product_type">
                        <?php foreach ($types as $id => $type): ?>
                            <option value="<?php echo absint($id) ?>" <?php ( isset( $product['fs_product_type'] ) )? selected($product['fs_product_type'], $id) : null; ?>><?php echo esc_html($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope = "row">Fastspring Subscription</th>
                <td>
                    <input type="checkbox" name="mwfi_product_subscription" value="1" <?php ( isset( $product['fs_product_subscription'] ) )? checked($product['fs_product_subscription'], 1) : null; ?>/>
                    <input type="number" name="mwfi_product_subscription_length" value="<?php echo ( isset( $product['fs_product_subscription_interval_length'] ) ) ? esc_html( $product['fs_product_subscription_interval_length'] ) : null ?>" size = "25" placholder = "Interval Length"/>
                    <select name="mwfi_product_subscription_interval" autocomplete = "off">
                        <option value="day" <?php ( isset( $product['fs_product_subscription_interval'] ) )? selected($product['fs_product_subscription_interval'], 'day') : null; ?>>Day</option>
                        <option value="week" <?php ( isset( $product['fs_product_subscription_interval'] ) )? selected($product['fs_product_subscription_interval'], 'week') : null; ?>>Week</option>
                        <option value="month" <?php ( isset( $product['fs_product_subscription_interval'] ) )? selected($product['fs_product_subscription_interval'], 'month') : null; ?>>Month</option>
                        <option value="year" <?php ( isset( $product['fs_product_subscription_interval'] ) )? selected($product['fs_product_subscription_interval'], 'year') : null; ?>>Year</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope = "row">FastSpring International Prices</th>
                <td>
                    <table>
                        <tr>
                            <th> AUD </th>
                            <td><input type="text" name="price_aud" value="<?php echo ( isset( $product['fs_price_aud'] ) ) ? esc_html( $product['fs_price_aud'] ) : null ?>" size = "25" placholder = "-"/></td>
                        </tr>
                        <tr>
                            <th> CAD </th>
                            <td><input type="text" name="price_cad" value="<?php echo ( isset( $product['fs_price_cad'] ) ) ? esc_html( $product['fs_price_cad'] ) : null ?>" size = "25" placholder = "-"/></td>
                        </tr>
                        <tr>
                            <th> EUR </th>
                            <td><input type="text" name="price_eur" value="<?php echo ( isset( $product['fs_price_eur'] ) ) ? esc_html( $product['fs_price_eur'] ) : null ?>" size = "25" placholder = "-"/></td>
                        </tr>
                        <tr>
                            <th> GBP </th>
                            <td><input type="text" name="price_gbp" value="<?php echo ( isset( $product['fs_price_gbp'] ) ) ? esc_html( $product['fs_price_gbp'] ) : null ?>" size = "25" placholder = "-"/></td>
                        </tr>
                        <tr>
                            <th> JPY </th>
                            <td><input type="text" name="price_jpy" value="<?php echo ( isset( $product['fs_price_jpy'] ) ) ? esc_html( $product['fs_price_jpy'] ) : null ?>" size = "25" placholder = "-"/></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    <?php
}


?>