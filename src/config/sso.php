<?php
return [
    'providers' => [
        'google' => [
            'client_id' => env('SSO_GOOGLE_CLIENT_ID'),
            'client_secret' => env('SSO_GOOGLE_CLIENT_SECRET'),
            'redirect' => env('SSO_GOOGLE_REDIRECT'),
        ],
        'github' => [
            'client_id' => env('SSO_GITHUB_CLIENT_ID'),
            'client_secret' => env('SSO_GITHUB_CLIENT_SECRET'),
            'redirect' => env('SSO_GITHUB_REDIRECT'),
        ],
        'facebook' => [
            'client_id' => env('SSO_FACEBOOK_CLIENT_ID'),
            'client_secret' => env('SSO_FACEBOOK_CLIENT_SECRET'),
            'redirect' => env('SSO_FACEBOOK_REDIRECT'),
        ],
        'twitter' => [
            'client_id' => env('SSO_TWITTER_CLIENT_ID'),
            'client_secret' => env('SSO_TWITTER_CLIENT_SECRET'),
            'redirect' => env('SSO_TWITTER_REDIRECT'),
        ],
        'jumpcloud' => [
            'client_id' => env('SSO_JUMPCLOUD_CLIENT_ID'),
            'client_secret' => env('SSO_JUMPCLOUD_CLIENT_SECRET'),
            'redirect' => env('SSO_JUMPCLOUD_REDIRECT'),
        ]
    ],
];
