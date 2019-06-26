<?php

use \SynergiTech\Postal\Models\Email;
use \SynergiTech\Postal\Models\Email\Webhook;

/**
 * Configuration options for synergitech/laravel-postal
 */
return [
    // this is the HTTPS URL of your Postal server
    'domain' => env('POSTAL_DOMAIN'),

    // this is an API credential in the same mail server
    // as the domain you wish to send from
    'key' => env('POSTAL_KEY'),

    'models' => [
        'email' => env('POSTAL_MODELS_EMAIL', Email::class),
        'webhook' => env('POSTAL_MODELS_WEBHOOK', Webhook::class),
    ],

    // enable features within this package
    // - note that webhookreceiving requires emaillogging to actually do anything
    'enable' => [
        'emaillogging' => env('POSTAL_ENABLE_EMAILLOG', true),
        'webhookreceiving' => env('POSTAL_ENABLE_WEBHOOKRECEIVE', true),
    ],

    'webhook' => [
        // route to receive webhooks, configure to avoid collisions with the rest of your app
        'route' => env('POSTAL_WEBHOOK_ROUTE', '/postal/webhook'),
        // attempt to verify the X-Postal-Signature header
        'verify' => env('POSTAL_WEBHOOK_VERIFY', true),
        // the public key, sourced from your servers DKIM record "p" value WITHOUT THE TRAILING SEMICOLON
        'public_key' => env('POSTAL_WEBHOOK_PUBLIC_KEY', ''),
    ],
];
