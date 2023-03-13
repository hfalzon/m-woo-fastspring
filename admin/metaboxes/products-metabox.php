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
    //Create a table for storing the tax code
    //And product type
    ?>
        <table class = "form-table">
            <tr valign="top">
                <th scope="row">Fastspring Tax Code</th>
                <td><input type="text" name="mwfi_product_path" value="" size = "25"/></td>
            </tr>
            <tr valign="top">
                <th scope="row">Fastspring Product Type</th>
                <td>
                    <select name="mwfi_product_type">
                        <option value="subscription">Subscription</option>
                        <option value="digital-download">Digital Download</option>
                        <option value="digital-access">Digital Access</option>
                        <option value="shiped-product">Shipped Product</option>
                        <option value="service">Service</option>
                    </select>
                </td>
        </table>
    <?php
}


?>