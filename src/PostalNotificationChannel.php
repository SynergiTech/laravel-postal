<?php

namespace SynergiTech\Postal;

use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class PostalNotificationChannel extends MailChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($notifiable, Notification $notification)
    {
        // reference MailChannel
        /** @phpstan-ignore-next-line */
        $message = $notification->toMail($notifiable);

        // remove the checks

        $this->mailer->send(
            $this->buildView($message),
            array_merge($message->data(), $this->additionalMessageData($notification)),
            $this->messageBuilder($notifiable, $notification, $message)
        );
        // fin
    }

    /**
     * Get the recipients of the given message.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @param  \Illuminate\Notifications\Messages\MailMessage  $message
     * @return mixed
     */
    protected function getRecipients($notifiable, $notification, $message)
    {
        $recipients = [];

        // check if routeNotificationForPostal is defined, or default to the mail driver
        foreach ([self::class, 'postal', 'mail'] as $driver) {
            /** @phpstan-ignore-next-line */
            $driverRecipients = $notifiable->routeNotificationFor($driver, $notification);

            if ($driverRecipients === null) {
                continue;
            }

            $recipients = $driverRecipients;
            if (is_string($recipients)) {
                $recipients = [$recipients];
            }

            break;
        }

        /** @var array<string|int,mixed> $recipients */
        return collect($recipients)->mapWithKeys(function ($recipient, $email) {
            return is_numeric($email)
                /** @phpstan-ignore-next-line */
                ? [$email => (is_string($recipient) ? $recipient : $recipient->email)]
                : [$email => $recipient];
        })->all();
    }

    /**
     * Build the mail message.
     *
     * @param  \Illuminate\Mail\Message  $mailMessage
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @param  \Illuminate\Notifications\Messages\MailMessage  $message
     * @return void
     */
    protected function buildMessage($mailMessage, $notifiable, $notification, $message)
    {
        parent::buildMessage($mailMessage, $notifiable, $notification, $message);

        // the model that the notification is based on should be returned
        // by a logEmailAgainstModel method in your notification
        if (method_exists($notification, 'logEmailAgainstModel')) {
            $model = $notification->logEmailAgainstModel();
            if ($model instanceof Model) {
                // todo use callbacks instead?
                $headers = $mailMessage->getSymfonyMessage()->getHeaders();
                $headers->addTextHeader('notifiable_class', get_class($model));

                /** @var string $modelKey */
                $modelKey = $model->getKey();
                $headers->addTextHeader('notifiable_id', $modelKey);
            }
        }
    }
}
