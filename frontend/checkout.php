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
?>