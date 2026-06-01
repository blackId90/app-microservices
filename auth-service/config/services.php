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

    'discord' => [
        'webhook_url' => env('DISCORD_WEBHOOK_URL'),
    ],

    //* Application Services
    'application' => [
        'api_key' => env('API_SERVICE_KEY'),
        'control_center_service' => [
            'service_name' => env('CONTROL_CENTER_SERVICE_NAME', 'ControlCenterService'),
            'url' => env('CONTROL_CENTER_SERVICE_URL', 'http://control-center-service:8000/api/v1/control-center/internal'),
            'timeout' => env('CONTROL_CENTER_SERVICE_TIMEOUT', 5),
            'retries' => env('CONTROL_CENTER_SERVICE_RETRIES', 3),
            'retry_delay' => env('CONTROL_CENTER_SERVICE_RETRY_DELAY', 200),
        ],
    ],

];
