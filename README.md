# Laravel Postal

This library integrates Postal with the standard Laravel 5 mail framework.

# Install

First, install the package using Composer:

```
composer require synergitech/laravel-composer
```

Next, add your credentials to your `.env` and set your mail driver to `postal`:

```
MAIL_DRIVER=postal

POSTAL_DOMAIN=
POSTAL_KEY=
```

Finally, add the Postal service to `config/services.php`:

```
'postal' => [
    'domain' => env('POSTAL_DOMAIN'),
    'key' => env('POSTAL_KEY'),
],
```

# Usage

As this is a driver for the main Laravel Mail framework, sending emails is the same as usual - just follow the Laravel Mail documentation.

The response returned by Postal is returned when you send an email, embedded as an object. This can also be accessed using the `Illuminate\Mail\Events\MessageSent` listener.
