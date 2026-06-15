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
    'mailjet' => [
        'key' => env('MAILJET_APIKEY'),
        'secret' => env('MAILJET_APISECRET'),
        'transactional' => [
            'call' => true,
            'options' => [
                'url' => 'api.mailjet.com',
                'version' => 'v3.1',
                'call' => true,
                'secured' => true
            ],
        ],
        'common' => [
            'call' => true,
            'options' => [
                'url' => 'api.mailjet.com',
                'version' => 'v3',
                'call' => true,
                'secured' => true
            ],
        ],
        'v4' => [
            'call' => true,
            'options' => [
                'url' => 'api.mailjet.com',
                'version' => 'v4',
                'call' => true,
                'secured' => true
            ]
        ],
    ],
    'resend' => [
        'api_key' => env('RESEND_API_KEY', 're_QdiydcHv_FfZMuBpdreedW1fDsBsfEz7a'),
        'domain' => env('RESEND_DOMAIN', 'academicdigital.space'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Push (VAPID) — Browser Push Notifications
    |--------------------------------------------------------------------------
    | Used by App\Services\Push\WebPushService and the public/sw.js service
    | worker to deliver OS-level push notifications when the tab is closed
    | or backgrounded. Generate keys ONCE via: php artisan vapid:generate
    */
    'webpush' => [
        'vapid_subject'     => env('VAPID_SUBJECT', 'mailto:cug@academicdigital.space'),
        'vapid_public_key'  => env('VAPID_PUBLIC_KEY'),
        'vapid_private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Adobe PDF Services — memo export attachment rendering
    |--------------------------------------------------------------------------
    | OAuth Server-to-Server credentials from https://developer.adobe.com/console.
    | Used by App\Services\Pdf\AdobePdfService to convert Word/Excel/PowerPoint
    | memo attachments into PDF and merge them into the exported memo PDF. When
    | client_id / client_secret are blank the export silently falls back to
    | listing those attachments by name only (no Adobe calls are made).
    */
    'adobe_pdf' => [
        'client_id'     => env('ADOBE_PDF_CLIENT_ID'),
        'client_secret' => env('ADOBE_PDF_CLIENT_SECRET'),
        'org_id'        => env('ADOBE_PDF_ORG_ID'),
    ],

];
