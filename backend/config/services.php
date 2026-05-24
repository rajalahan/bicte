<?php

return [
    'mailgun'  => ['domain' => env('MAILGUN_DOMAIN'), 'secret' => env('MAILGUN_SECRET')],
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'ses'      => ['key' => env('AWS_ACCESS_KEY_ID'), 'secret' => env('AWS_SECRET_ACCESS_KEY'), 'region' => env('AWS_DEFAULT_REGION', 'us-east-1')],
    'resend'   => ['key' => env('RESEND_KEY')],
    'slack'    => ['notifications' => ['bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'), 'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL')]],

    // Bicte: AI assistant + SMS gateway
    'assistant' => [
        'provider' => env('ASSISTANT_PROVIDER', 'local'),
        'model'    => env('ASSISTANT_MODEL'),
        'api_key'  => env('ASSISTANT_API_KEY'),
        'endpoint' => env('ASSISTANT_ENDPOINT'),
    ],
    'sms' => [
        'provider'  => env('SMS_PROVIDER'),
        'api_key'   => env('SMS_API_KEY'),
        'sender_id' => env('SMS_SENDER_ID', 'Lahan_Mun'),
    ],
];
