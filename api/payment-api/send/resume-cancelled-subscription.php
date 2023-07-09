<?php defined('ABSPATH') or die();

function mwfi_resume_cancled_sub( string $path )
{
    //Check if user is logged in
    if (!is_user_logged_in()) return false;
    //Check if user is subscribed
    $user_id = get_current_user_id();
    //Check path is a valid string with only letters, numbers, dashes and underscores
    if (!preg_match('/^[a-zA-Z0-9-_]+$/', $path)) return false;
    //Get subscription id
    global $wpdb;
    $subscription = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mwfi_subscriptions WHERE user_id = '%d' AND subscription_status = 1 AND subscription_end IS NOT NULL AND fs_product_path = '%s'", $user_id, $path));
    if (!$subscription) return false;

    //Check if the subscription has ended by comparing the end date to the current date
    $current_date = new DateTime('now');
    $subscription_end = new DateTime($subscription->subscription_end);
    if ($current_date > $subscription_end) return false;
    //Create a resume subscription request

    $headers = mwfi_create_headers();

    $resume_request = mwfi_curl_request(
        'https://api.fastspring.com/subscriptions/' . $subscription->fs_subscription_id,
        'POST',
        array(
            'subscriptions' => array(
                array(
                    'subscription' => $subscription->fs_subscription_id,
                    'deactivation' => null
                )
            )
        ),
        $headers
    );

    //Debug - see what the package looks like with json

    //Check if resume request was successful
    if ($resume_request['httpCode'] != 200) return false;

    //Update the subscription and remove the subscription end date and set the subscription_next_payment to the current end_date
    $update = $wpdb->update(
        $wpdb->prefix . 'mwfi_subscriptions',
        array(
            'subscription_end' => null,
            'subscription_next_payment' => $subscription->subscription_end,
        ),
        array(
            'user_id' => $user_id,
            'subscription_status' => true,
            'fs_product_path' => $path,
        ),
        array(
            '%s',
            '%s'
        ),
        array(
            '%d',
            '%d',
            '%s',
        )
    );

    return true;
}