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
        'email' => Email::class,
        'webhook' => Webhook::class,
    ],

    // enable features within this package
    // - note that webhookreceiving requires emaillogging to actually do anything
    'enable' => [
        'emaillogging' => true,
        'webhookreceiving' => true,
    ],

    // route to receive webhooks, configure to avoid collisions with the rest of your app
    'webhookroute' => '/postal/webhook',
];
