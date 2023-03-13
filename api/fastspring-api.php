<?php 
defined('ABSPATH') or die(); 

//Get the admin api files
//Sends
require MWFI_PATH . 'api/admin-api/send/create-update-product.php';
//Gets
require MWFI_PATH . 'api/admin-api/get/get-product.php';
require MWFI_PATH . 'api/admin-api/get/get-products.php';
require MWFI_PATH . 'api/admin-api/get/get-subscriptions.php';

//Deletes
require MWFI_PATH . 'api/admin-api/delete/delete-product.php';

?>