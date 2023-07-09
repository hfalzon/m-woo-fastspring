<?php defined('ABSPATH') or die();

function mwfi_change_sub(string $from_path, string $to_path)
{
    //Check if user is logged in
    if (!is_user_logged_in()) return false;

    //Check if user is subscribed
    $user_id = get_current_user_id();

    //Check path is a valid string with only letters, numbers, dashes and underscores
    if (!preg_match('/^[a-zA-Z0-9-_]+$/', $from_path)) return false;
    if (!preg_match('/^[a-zA-Z0-9-_]+$/', $to_path)) return false;

    //Get subscription id
    global $wpdb;
    $subscription = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mwfi_subscriptions WHERE user_id = '%d' AND subscription_status = 1 AND fs_product_path = '%s'", $user_id, $from_path));
    if (!$subscription) return false;

    //Check if subscription is active
    if ($subscription->subscription_status != true) return false;

}