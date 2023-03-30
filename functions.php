<?php defined('ABSPATH') or die();

function mwfi_encrypt( $data, $key = 'QWsdnoai8qwndoskSFH0Aks-aAKSDHSNAasSdAGWa' )
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    $encrypted = base64_encode($iv . $encrypted);
    return $encrypted;
}

function mwfi_decrypt( $encrypted, $key = 'QWsdnoai8qwndoskSFH0Aks-aAKSDHSNAasSdAGWa' )
{
    $encrypted = base64_decode($encrypted);
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($encrypted, 0, $iv_length);
    $encrypted = substr($encrypted, $iv_length);
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return $decrypted;
}

class mwfi_product_data
{
    public $type;
    public $id;
    public $product_type;
    public $tax_code;
    public $prices = array(

    );

    private $types =  array(
        '0' => 'digital',
        '1' => 'physical',
        '2' => 'digital-physical',
    );

    private $tax_codes = array(

    );

    private $formats = array(
        'digital',
        'physical',
        'digital-physical',
    );

    public function get_types()
    {
        return $this -> types;
    }

    public function __construct( $id, $type = null )
    {
        if ( $id == null && $type == null ) {
            return;
        }
        if ( $id == null ) {
            $this -> set_type( $type );
            $this -> get_id();
        } else {
            $this -> set_id( $id );
            $this -> get_type();
        }
    }

    private function set_id( $id )
    {
        //Check if id is valid
        if ( ! array_key_exists( $id, $this -> formats ) ) {
            return;
        }
        $this -> id = absint( $id );
    }

    private function set_type( $type )
    {
        //Check if format is valid
        if ( ! in_array( $type, $this -> formats ) ) {
            return;
        }
        $this -> type = sanitize_text_field( $type );
    }

    private function get_type()
    {
        //Check if id is set
        if ( ! isset( $this -> id ) ) {
            return;
        }
        $id = $this -> id;

        //Get the type
        $this -> type = $this -> types[ $id ]; //Get the type of product
    }

    private function get_id()
    {
        //Check if type is set
        if ( ! isset( $this -> type ) ) {
            return;
        }
        $type = $this -> type;

        //Get the id
        $this -> id = array_search( $type, $this -> formats );
    }

    private function get_product()
    {
        //Check if id is set
        if ( ! isset( $this -> id ) ) {
            return;
        }
        $id = $this -> id;

        //Get the product
        $product = $wpdb->get_row( $wpdb -> prepare("SELECT * FROM $table_name WHERE wc_product_id = '%d'", $post->ID), ARRAY_A );
        if ( $product == null ) {
            return;
        }
        $this -> product_type = $product['product_type'];
        $this -> tax_code = $product['tax_code'];
        $this -> prices = array(
            'AUD' => ( isset($product['price_aud'])) ? $product['price_aud'] : null,
            'GBP' => ( isset($product['price_gbp'])) ? $product['price_gbp'] : null,
            'EUR' => ( isset($product['price_eur'])) ? $product['price_eur'] : null,
            'JPY' => ( isset($product['price_jpy'])) ? $product['price_jpy'] : null,
            'CAD' => ( isset($product['price_cad'])) ? $product['price_cad'] : null,
        );
    }

}

class mwf_product
{
    public $id;
    public $type;
    public $tax_code;
    public $prices = array(

    );
    public $subscription;
    public $subscription_interval;
    public $subscription_interval_count;
}

function mwfi_get_product_data( $id )
{
    $product_data = new mwfi_product_data( $id );
    return $product_data;
}

function mwfi_create_headers()
{
    $username = get_option('mwfi_api_key');
    $password = mwfi_decrypt( get_option('mwfi_api_secret_key') );
    $auth = base64_encode($username . ':' . $password);

    return array( 'authorization: Basic ' . $auth );
}

function mwfi_checkout()
{
    //Check if woo is active
    if ( ! class_exists( 'WooCommerce' ) ) {
        return false;
    }
    $session = mwfi_create_session();
    //Check if session is valid
    if ( isset( $session['error'] ) ) {
        return $session['error'];
    }
    $session_id = $session['session_id'];
    //Validate session id via regex, only letters, numbers, underscore and dashes
    if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $session_id ) ) {
        return false;
    }
    return $session_id;
}

/**
 * Get Price with Decimals
 * @param  object $product Product Object
 * @param  bool   $strict  If true, will add .00 to price if it doesn't have decimals
 * @return string          Price with decimals
 */
function mwfi_get_price( $product, $strict = false )
{
    $price = $product -> get_price();
    $price = abs( $price ); //Make sure price is positive number
    //Check if price has decimals
    if ( strpos( $price, '.' ) === false ) {
        if ( !$strict ) {
            return $price;
        }
        $price .= '.00';
    }
    $price = number_format( $price, 2, '.', '' );
    return $price;
}
?>