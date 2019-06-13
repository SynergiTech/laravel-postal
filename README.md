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
