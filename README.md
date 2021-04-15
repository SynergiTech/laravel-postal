# Laravel Postal
![Tests](https://github.com/SynergiTech/laravel-postal/workflows/Tests/badge.svg)

This library integrates [Postal](https://github.com/postalhq/postal) with the standard Laravel mail framework.

## Install

First, install the package using Composer:

```
composer require synergitech/laravel-postal
```
Next, run the package migrations:
```
php artisan migrate
```

Next, add your credentials to your `.env` and set your mail driver to `postal`:

```
MAIL_MAILER=postal

POSTAL_DOMAIN=https://your.postal.server
POSTAL_KEY=yourapicredential
```

Notice: if you're using Laravel < 7, `MAIL_MAILER` should be `MAIL_DRIVER`.

Finally, if you're using Laravel 7 or later, add postal as a mailer to your `config/mail.php` file

```
'mailers' => [
    'postal' => [
        'transport' => 'postal',
    ],
],
```

If you want to alter the configuration further, you can reference the `config/postal.php` file for the keys to place in your environment. Alternatively, you can publish the config file in the usual way if you wish to make specific changes:
```
php artisan vendor:publish --provider="SynergiTech\Postal\PostalServiceProvider"
```

Also make sure you have filled out who the email comes from and that the domain you use is authorised by the API credential.

```
MAIL_FROM_ADDRESS=noreply@your.company
MAIL_FROM_NAME="Your Company"
```

## Usage

As this is a driver for the main Laravel Mail framework, sending emails is the same as usual - just follow the Laravel Mail documentation - however we recommend you make use of the `PostalNotificationChannel` class to enable full email tracking within your software.

## Upgrading
### Upgrading to V3
If you are updating to Laravel 7 as well, you will need to update your environment variable names.

### Upgrading from V1 to V2
**Please note** version 2 is backwards compatible with version 1 as long as you are not using a listener. Version 2 is also configured differently and includes many more features (including support for webhooks) so if you're upgrading from version 1, please take time to re-read this information.

### Upgrading between V2 and V2.1
There are no backwards incompatible changes between these two versions unless you have customized the default table names. Prior to v2.1, we published our migration files into your application. Beginning in v2.1, we now present these to Laravel in our service provider.

Our migrations will be run again when upgrading between these two versions. The migrations will not recreate the table or otherwise error when it detects the presence of the default tables. However, if they have been renamed, they will be created again. Simply create a new migration to drop the tables.

### Logging messages sent against notifiable models

Create an `email` notification as you would normally but have `'SynergiTech\Postal\PostalNotificationChannel'` or `PostalNotificationChannel::class` returned in the `via()` method.

In order to associate the messages with the notifiable model, you will need to return the model object in a method called `logEmailAgainstModel` in your notification class. If you do not include this method, the messages will still be logged (if you have that enabled in the config) but the link back to your notifiable model will not be created.

Here is a complete example of what you need to do to ensure your notifiable model is linked to the emails that get sent.

```php
use Notification;
use SynergiTech\Postal\PostalNotificationChannel;
use App\Notifications\EnquiryNotification;

// controller code here

Notification::route(PostalNotificationChannel::class, $to)
    ->notify(new EnquiryNotification($enquiry));
```

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use SynergiTech\Postal\PostalNotificationChannel;

class EnquiryNotification extends Notification
{
    private $enquiry;

    public function __construct($enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function via($notifiable)
    {
        return [PostalNotificationChannel::class];
    }

    public function logEmailAgainstModel()
    {
        return $this->enquiry;
    }

    public function toMail($notifiable)
    {
        // message constructed here
    }
}
```

You can still send messages through Postal as a driver if you just leave `'mail'` in the `via()` method but the channel from this package is responsible for creating the link so if you do not use `PostalNotificationChannel` as mentioned above, there will not be a link between the messages and your notifiable model.

**Please note** that Postals PHP client can throw exceptions if it fails to submit the message to the server (i.e. a permission problem occurred or an email address wasn't valid) so if you have a process which relies on sending an email, it would be advisable to send the notification before proceeding (i.e. saving the updated object to the database).

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

## Webhooks

This package also provides the ability for you to record webhooks from Postal. This functionality is enabled by default.

### Verifying the webhook signature

Each webhook payload should include a couple of unique values for some level of accuracy in your webhooks but if you want to verify the signature, you must provide the signing key from your Postal and enable this feature.

You can access the signing public key by running `postal default-dkim-record` on your Postal server and copying the value of the `p` parameter (excluding the semicolon) to your environment under the key `POSTAL_WEBHOOK_PUBLIC_KEY`.

## Listeners

As with default Laravel, you can make use of the `Illuminate\Mail\Events\MessageSent` listener. In version 1, you received the whole response from Postal however in version 2 you will only receive a `Postal-Message-ID` and this is contained in the message header. This will allow you to access the emails created as this will be the value of the `postal_email_id` column.

The change allows your code to meet static analysis requirements.

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
        $headers = $event->message->getHeaders();
        $postalmessageid = $headers->get('Postal-Message-ID');
        $postalmessageid = explode(': ', $postalmessageid);
        $postalmessageid = (count($postalmessageid) == 1) ? '' : trim($postalmessageid[1]);

        if (strlen($postalmessageid) > 0) {
            // do something here
        }
    }
}
```

## Running tests
To run the full suite of unit tests:
```
vendor/bin/phpunit -c phpunit.xml
```
You will need xdebug installed to generate code coverage.

### Docker
A sample Dockerfile is provided to setup an environment to run the tests without configuring your local machine. The Dockerfile can test multiple combinations of versions for PHP and Laravel via arguments.

```
docker build . --build-arg PHP_VERSION=7.3 --build-arg LARAVEL=7
```
