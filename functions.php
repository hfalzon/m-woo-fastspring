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

class mwfi_get_product_data
{
    public $format;
    public $type;
    public $id;

    private $types =  array(
        0 => 'digital_download',         //0
        1 => 'digital',                  //1
        2 => 'digital_subscription',     //2
        3 => 'digital_lessons',          //3
        4 => 'physical',                 //4
        5 => 'physical_subscription',    //5
        6 => 'physical_lessons',         //6
    );

    private $formats = array(
        'digital',
        'physical',
        'digital-physical',
    );

    public function __construct( $id = null, $type = null )
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
        $this -> get_format();
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
        $this -> type = $this -> formats[ $id ];
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

    private function get_format()
    {
        //Check if type is set
        if ( ! isset( $this -> type ) ) {
            return;
        }
        $type = $this -> type;

        //Get the format
        if ( in_array( $type, array( 'digital', 'digital_subscription', 'digital_lessons' ) ) ) {
            $this -> format = 'digital';
        } elseif ( in_array( $type, array( 'physical', 'physical_subscription', 'physical_lessons' ) ) ) {
            $this -> format = 'physical';
        } else {
            $this -> format = 'digital-physical';
        }
    }
}

?>