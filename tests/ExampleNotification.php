<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SynergiTech\Postal\PostalNotificationChannel;

class ExampleNotification extends Notification
{
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function via($notifiable)
    {
        return [PostalNotificationChannel::class];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage())
            ->subject('Example Notification');

        return $message;
    }

    public function logEmailAgainstModel()
    {
        return $this->model;
    }
}
