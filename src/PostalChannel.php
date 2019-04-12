<?php

namespace SynergiTech\Postal;

use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;

class PostalChannel extends MailChannel
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

        // link the emails to the model that sent them
        $emailmodel = config('postal.models.email');
        \DB::table((new $emailmodel)->getTable())
            ->where('postal_email_id', $message->getSwiftMessage()->getHeaders()->get('Message-ID')) // THIS DOESN'T WORK
            ->update([
                'emailable_type' => get_class($notifiable),
                'emailable_id' => $notifiable->id,
            ]);
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

        return collect($recipients)->mapWithKeys(function ($recipient, $email) {
            return is_numeric($email)
                    ? [$email => (is_string($recipient) ? $recipient : $recipient->email)]
                    : [$email => $recipient];
        })->all();
    }
}
