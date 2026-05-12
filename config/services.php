<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'msg91' => [
        'key' => env('MSG91_API_KEY'),
        'sender' => env('MSG91_SENDER_ID'),
        'template_id' => env('MSG91_TEMPLATE_ID'),
        'country_code' => env('MSG91_COUNTRY_CODE', '91'),
        'base_url' => env('MSG91_BASE_URL', 'https://api.msg91.com/api/v5/otp'),
    ],

    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID', 'rzp_test_1DP5mmOlF5G5ag'),
        'key_secret' => env('RAZORPAY_KEY_SECRET', 'rzorpay_secret'),
    ],

];
