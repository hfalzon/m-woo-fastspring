<?php
defined('ABSPATH') or die();

/**
 * Create a settings page for the plugin that will allow the user to enter their Fastspring credentials
 * Add the page to the settings menu in the admin dashboard
 */

function mwfi_add_settings_page()
{
    add_options_page(
        'Mythic WooCommerce Fastspring Integration Settings',
        'Mythic WC Fastspring',
        'manage_options',
        'mwfi-settings',
        'mwfi_settings_page'
    );
}

add_action('admin_menu', 'mwfi_add_settings_page');

/**
 * Create the settings page
 */

function mwfi_settings_page()
{
    ?>
    <div class="wrap">
        <h1>Mythic WooCommerce Fastspring Integration Settings</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php settings_fields('mwfi-settings-group'); ?>
            <?php do_settings_sections('mwfi-settings-group'); ?>
            <?php wp_nonce_field( 'mythic-admin-setting-nonce', 'mwfi-settings-nonce' ); ?>
            <table class="form-table">
                <tr valign="top">
                <th scope="row">Fastspring API Key</th>
                <td><input type="text" name="mwfi_api_key" value="<?php echo esc_attr(get_option('mwfi_api_key')); ?>" size = "50"/></td>
                </tr>
                <tr valign="top">
                <th scope="row">Fastspring API Secret Key</th>
                <td><input type="text" name="mwfi_api_secret_key" value="<?php echo esc_attr( (get_option('mwfi_api_secret_key'))? '************' : null ); ?>" size = "50"/></td>
                </tr>
            </table>
            <input type ="hidden" name = "action" value = "mwfi_save_settings">
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register the settings and validate the input
function mwfi_register_settings() {
    register_setting(
      'mwfi-settings-group',
      'mwfi_api_key',
      'mwfi_validate_api_key'
    );
    register_setting(
      'mwfi-settings-group',
      'mwfi_api_secret_key',
      'mwfi_validate_api_secret_key'
    );
  }
  add_action('admin_init', 'mwfi_register_settings');

// Validate the API Key
function mwfi_validate_api_key($input)
{
    // Check if the input is valid
    if ( ! isset( $input ) ) {
        wp_die( 'Invalid' );
    }
    //Check key and secret key against Regex only Letters dashes
    if ( ! preg_match( '/^[a-zA-Z0-9-]+$/', $input ) ) {
        wp_die( 'Invalid key' );
    }
    return $input;
}
function mwfi_validate_api_secret_key($input)
{
    // Check if the input is valid
    if ( ! isset( $input ) ) {
        wp_die( 'Invalid' );
    }
    //Check key and secret key against Regex that matches a base64 string
    if ( ! preg_match( '/^[a-zA-Z0-9\/+]*={0,2}$/', $input ) ) {
        wp_die( 'Invalid Secret key' );
    }
    return $input;
}
function mwfi_save_settings()
{
    //wp_die( var_dump($_POST) );
    //Get post
    $data = $_POST;
    // Check if the nonce is valid
    if ( ! wp_verify_nonce( $data['mwfi-settings-nonce'], 'mythic-admin-setting-nonce' ) ) {
        wp_die( 'Invalid nonce' );
    }
    // Check if the user has permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Invalid user' );
    }
    // Check if the input is valid
    if ( ! isset( $data['mwfi_api_key'] ) || ! isset( $data['mwfi_api_secret_key'] ) ) {
        wp_die( 'Invalid data' );
    }
    // Save the data
    update_option( 'mwfi_api_key', $data['mwfi_api_key'] );
    update_option( 'mwfi_api_secret_key', mwfi_encrypt($data['mwfi_api_secret_key'], 'QWsdnoai8qwndoskSFH0Aks-aAKSDHSNAasSdAGWa' ) );

}

  add_action('admin_post_mwfi_save_settings', 'mwfi_save_settings');