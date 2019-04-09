# Laravel Postal

This library integrates [Postal](https://github.com/atech/postal) with the standard Laravel 5 mail framework.

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

Also make sure you have filled out who the email comes from and that the domain you use is authorised by the API credential.

```
MAIL_FROM_ADDRESS=noreply@your.company
MAIL_FROM_NAME="Your Company"
```

Finally, add the Postal service to `config/services.php`:

```php
'postal' => [
    'domain' => env('POSTAL_DOMAIN'),
    'key' => env('POSTAL_KEY'),
],
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