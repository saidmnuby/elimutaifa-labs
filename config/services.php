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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'github' => [
        'client_id' => env('GITHUB_OAUTH_CLIENT_ID'),
        'client_secret' => env('GITHUB_OAUTH_CLIENT_SECRET'),
        'redirect_uri' => env('GITHUB_OAUTH_REDIRECT_URI'),
        'app_id' => env('GITHUB_APP_ID'),
        'app_slug' => env('GITHUB_APP_SLUG', 'elimutaifa-labs'),
        'app_private_key_path' => env('GITHUB_APP_PRIVATE_KEY_PATH'),
        'app_webhook_secret' => env('GITHUB_APP_WEBHOOK_SECRET'),
        'deploy_dispatch_token' => env('GITHUB_DEPLOY_DISPATCH_TOKEN'),
        'deploy_repository' => env('GITHUB_DEPLOY_REPOSITORY', 'saidmnuby/mitandao'),
        'deploy_workflow' => env('GITHUB_DEPLOY_WORKFLOW', 'deploy.yml'),
        'deploy_ref' => env('GITHUB_DEPLOY_REF', 'main'),
        'deploy_callback_secret' => env('GITHUB_DEPLOY_CALLBACK_SECRET'),
    ],

    'selcom' => [
        'base_url' => env('SELCOM_BASE_URL'),
        'api_key' => env('SELCOM_API_KEY'),
        'api_secret' => env('SELCOM_API_SECRET'),
        'vendor_id' => env('SELCOM_VENDOR_ID'),
        'monthly_price' => (int) env('SELCOM_PLUS_MONTHLY_PRICE', 3000),
        'yearly_price' => (int) env('SELCOM_PLUS_YEARLY_PRICE', 20000),
    ],

];
