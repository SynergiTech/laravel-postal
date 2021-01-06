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
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // reference MailChannel
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
        // copy from mailchannel, update channel name to this class
        // - this is where the "to" addressee gets injected
        if (is_string($recipients = $notifiable->routeNotificationFor(self::class, $notification))) {
            $recipients = [$recipients];
        }

        // if there are no recipients, its probable that the shorter `->notify()` form was used
        if ($recipients === null) {
            if (is_string($recipients = $notifiable->routeNotificationFor('postal', $notification))) {
                $recipients = [$recipients];
            }
        }

        return collect($recipients)->mapWithKeys(function ($recipient, $email) {
            return is_numeric($email)
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
                $headers = $mailMessage->getSwiftMessage()->getHeaders();
                $headers->addTextHeader('notifiable_class', get_class($model));
                $headers->addTextHeader('notifiable_id', $model->id);
            }
        }
    }
}
