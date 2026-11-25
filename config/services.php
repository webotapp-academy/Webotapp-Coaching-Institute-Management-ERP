<?php

return [
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'resend' => ['key' => env('RESEND_KEY')],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'livekit' => [
        'url' => env('LIVEKIT_URL'),
        'api_key' => env('LIVEKIT_API_KEY'),
        'api_secret' => env('LIVEKIT_API_SECRET'),
        'recording_bucket' => env('LIVEKIT_RECORDING_BUCKET', env('AWS_BUCKET')),
        'recording_access_key' => env('LIVEKIT_RECORDING_ACCESS_KEY', env('AWS_ACCESS_KEY_ID')),
        'recording_secret_key' => env('LIVEKIT_RECORDING_SECRET_KEY', env('AWS_SECRET_ACCESS_KEY')),
        'recording_region' => env('LIVEKIT_RECORDING_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),\n        'recording_endpoint' => env('LIVEKIT_RECORDING_ENDPOINT', env('AWS_ENDPOINT')),\n        'recording_force_path_style' => env('LIVEKIT_RECORDING_FORCE_PATH_STYLE', env('AWS_USE_PATH_STYLE_ENDPOINT', false)),
    ],
];
