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
        <table class = "form-table">
            <tr valign="top">
                <th scope="row">Fastspring Tax Code</th>
                <td><input type="text" name="mwfi_product_taxcode" value="<?php echo esc_html( $product['fs_taxcode'] ) ?>" size = "25"/></td>
            </tr>
            <tr valign="top">
                <th scope="row">Fastspring Product Type</th>
                <td>
                    <?php $types = ( new mwfi_product_data( get_the_ID() ) ) -> get_types(); ?>
                    <select name="mwfi_product_type">
                        <?php foreach ($types as $id => $type): ?>
                            <option value="<?php echo absint($id) ?>" <?php selected($product['fs_product_type'], $id); ?>><?php echo esc_html($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
        </table>
    <?php
}


?>