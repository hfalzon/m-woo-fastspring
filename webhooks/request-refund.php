<?php defined('ABSPATH') or die;

//Rest Path for cancelling a subscription - This is more of a check... if the cancel request was initiated from the frontend, then the frontend will handle the cancellation. If the cancellation was initiated from fastspring, the the webhook will initiate the cancelation