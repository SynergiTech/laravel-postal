<?php

/**
 * Configuration options for synergitech/laravel-postal
 */
return [
    // this is the HTTPS URL of your Postal server
    'domain' => env('POSTAL_DOMAIN'),

    // this is an API credential in the same mail server
    // as the domain you wish to send from
    'key' => env('POSTAL_KEY'),
];
