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
        $fs_product_id = false;
        foreach ($mwfi_data as $product)
        {
            if ($product['wc_product_id'] == $post_id)
            {
                $fs_product_id = $product['fs_product_path'];
                break;
            }
        }
        //Display product path
        if ($fs_product_id)
        {
            echo esc_html( '<a href="https://fastspring.com/' . $fs_product_path . '" target="_blank">' . $fs_product_path . '</a>' );
        }
        else
        {
            echo 'Not linked';
        }
    }
}

?>