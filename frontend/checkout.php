<?php defined('ABSPATH') or die();

function mwfi_handle_checkout( $purchase_data )
{
    
}

add_action('wp_head', 'mwfi_fs_checkout');
function mwfi_fs_checkout()
{
    ?>
        <script
            id="fsc-api"
            src="https://sbl.onfastspring.com/sbl/0.9.4/fastspring-builder.min.js"
            type="text/javascript"
            data-storefront="blendertutorials.test.onfastspring.com/popup-blendertutorials">
        </script>
    <?php
}
//fastspring.builder.add('test-product');
//fastspring.builder.checkout();
remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
add_filter( 'woocommerce_checkout_redirect_empty_cart', '__return_false' );

function remove_checkout_page() {
    global $post;

    if ( is_wc_endpoint_url( 'order-received' ) ) {
        return;
    }

    if ( $post->ID == get_option( 'woocommerce_checkout_page_id' ) ) {
        wp_redirect( get_permalink( get_option( 'woocommerce_cart_page_id' ) ) );
        exit;
    }
}

add_action( 'template_redirect', 'remove_checkout_page' );
?>