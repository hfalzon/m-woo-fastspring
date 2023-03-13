<?php defined('ABSPATH') or die(); 

add_filter('manage_edit-product_columns', 'mwfi_add_product_columns');

function mwfi_add_product_columns($columns)
{
    $columns['mwfi_product_path'] = 'Fastspring';
    return $columns;
}

add_action('manage_product_posts_custom_column', 'mwfi_add_product_column_content', 10, 2);
function mwfi_add_product_column_content($column, $post_id)
{
    static $mwfi_data;
    //Get product and path link from db
    if ( empty($mwfi_data) )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mwfi_products';
        $query = "SELECT * FROM $table_name";
        $mwfi_data = $wpdb->get_results($query, ARRAY_A);
    }
    if ($column == 'mwfi_product_path')
    {
        //Get product path if it exists
        $fs_product_path = false;
        foreach ($mwfi_data as $product)
        {
            if ($product['wc_product_id'] == $post_id)
            {
                $fs_product_path = $product['fs_product_path'];
                break;
            }
        }
        //Display product path
        if ($fs_product_path)
        {
            echo '<a href="https://fastspring.com/' . esc_html($fs_product_path) . '" target="_blank">' . esc_html($fs_product_path) . '</a>';
        }
        else
        {
            echo 'Not linked';
        }
    }
}

?>