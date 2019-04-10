# Laravel Postal

This library integrates [Postal](https://github.com/atech/postal) with the standard Laravel 5 mail framework.

**Please note** version 2 is configured differently and includes many more features (including support for webhooks) so if you're upgrading from version 1, please take time to re-read this information.

## Install

First, install the package using Composer:

```
composer require synergitech/laravel-postal
```

Next, add your credentials to your `.env` and set your mail driver to `postal`:

```
MAIL_DRIVER=postal

POSTAL_DOMAIN=https://your.postal.server
POSTAL_KEY=yourapicredential
```

If you want to alter the configuration, you can publish the file and update it as you desire.

Also make sure you have filled out who the email comes from and that the domain you use is authorised by the API credential.

```
MAIL_FROM_ADDRESS=noreply@your.company
MAIL_FROM_NAME="Your Company"
```

## Usage

As this is a driver for the main Laravel Mail framework, sending emails is the same as usual - just follow the Laravel Mail documentation.

The response returned by Postal is returned when you send an email, embedded as an object. This can also be accessed using the `Illuminate\Mail\Events\MessageSent` listener.

```php
namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;

class MessageSentListener
{
    /**
     * Handle the event.
     *
     * @param MessageSent $event
     * @return void
     */
    public function handle(MessageSent $event)
    {
       if ($event->message->postal) {
            // do something here
       }
    }
}
```

### Send all email to one address (i.e. for development)

Our [similar package for FuelPHP](https://github.com/SynergiTech/fuelphp-postal) allows you to send all messages to a specific email address defined in your environment. Laravel already has a mechanism for this and you can use it by updating the `config/mail.php` file as follows:

```php
$config = [
    // existing config array
];

if (getenv('EMAIL')) {
    $config['to'] = [
        'address' => getenv('EMAIL'),
        'name' => 'Your Name'
    ];
}

return $config;
```
