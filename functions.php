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

?>