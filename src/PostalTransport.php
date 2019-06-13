<?php

namespace SynergiTech\Postal;

use Illuminate\Mail\Transport\Transport;

use Postal\SendMessage;
use Postal\Client;
use Postal\Error;
use Postal\SendResult;

use Swift_Attachment;
use Swift_Image;
use Swift_MimePart;
use Swift_Mime_SimpleMessage;

class PostalTransport extends Transport
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Inheritdoc from Swift_Transport
     *
     * @param Swift_Mime_SimpleMessage $swiftmessage
     * @param string[]                 $failedRecipients An array of failures by-reference
     *
     * @return int the number of sent messages
     */
    public function send(Swift_Mime_SimpleMessage $swiftmessage, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($swiftmessage);

        $postalmessage = $this->swiftToPostal($swiftmessage);

        try {
            $response = $postalmessage->send();
        } catch (Error $error) {
            throw new \BadMethodCallException($error->getMessage(), $error->getCode(), $error);
        }

        $this->sendPerformed($swiftmessage);

        $headers = $swiftmessage->getHeaders();

        // send known header back for laravel to match emails coming out of Postal
        $headers->addTextHeader('Message-ID', $response->result->message_id);

        if (config('postal.enable.emaillogging') === true) {
            function getHeaderValue($header)
            {
                $value = explode(': ', $header);
                if (count($value) == 1) {
                    return '';
                }

                // trim definitely required
                return trim($value[1]);
            }

            $this->recordEmailsFromResponse($swiftmessage, $response);

            $emailable_type = getHeaderValue($headers->get('notifiable_class'));
            $emailable_id = getHeaderValue($headers->get('notifiable_id'));

            // headers only set if using PostalNotificationChannel
            if ($emailable_type != '' && $emailable_id != '') {
                $emailmodel = config('postal.models.email');
                \DB::table((new $emailmodel)->getTable())
                    ->where('postal_email_id', $response->result->message_id)
                    ->update([
                        'emailable_type' => $emailable_type,
                        'emailable_id' => $emailable_id,
                    ]);
            }
        }

        return $this->numberOfRecipients($swiftmessage);
    }

    /**
     * Convert Swift message into a Postal sendmessage
     *
     * @param Swift_Mime_SimpleMessage $swiftmessage
     *
     * @return SendMessage the resulting sendmessage
     */
    private function swiftToPostal(Swift_Mime_SimpleMessage $swiftmessage) : SendMessage
    {
        // SendMessage cannot be reset so must be instantiated for each use
        $postalmessage = new SendMessage($this->client);

        $recipients = [];
        foreach (['to', 'cc', 'bcc'] as $type) {
            foreach ((array) $swiftmessage->{'get' . ucwords($type)}() as $email => $name) {
                // dedup recipients
                if (! in_array($email, $recipients)) {
                    $recipients[] = $email;
                    $postalmessage->{$type}($name != null ? ($name . ' <' . $email . '>') : $email);
                }
            }
        }

        if ($swiftmessage->getFrom()) {
            foreach ($swiftmessage->getFrom() as $email => $name) {
                $postalmessage->from($name != null ? ($name . ' <' . $email . '>') : $email);
            }
        }

        if ($swiftmessage->getReplyTo()) {
            foreach ($swiftmessage->getReplyTo() as $email => $name) {
                $postalmessage->replyTo($name != null ? ($name . ' <' . $email . '>') : $email);
            }
        }

        if ($swiftmessage->getSubject()) {
            $postalmessage->subject($swiftmessage->getSubject());
        }

        if ($swiftmessage->getContentType() == 'text/plain') {
            $postalmessage->plainBody($swiftmessage->getBody());
        } elseif ($swiftmessage->getContentType() == 'text/html') {
            $postalmessage->htmlBody($swiftmessage->getBody());
        } else {
            foreach ($swiftmessage->getChildren() as $child) {
                if ($child instanceof Swift_MimePart && $child->getContentType() == 'text/plain') {
                    $postalmessage->plainBody($child->getBody());
                }
            }
            $postalmessage->htmlBody($swiftmessage->getBody());
        }

        foreach ($swiftmessage->getChildren() as $attachment) {
            if ($attachment instanceof Swift_Attachment) {
                $postalmessage->attach(
                    $attachment->getFilename(),
                    $attachment->getContentType(),
                    $attachment->getBody()
                );
            } elseif ($attachment instanceof Swift_Image) {
                $postalmessage->attach(
                    $attachment->getId(),
                    $attachment->getContentType(),
                    $attachment->getBody()
                );
            }
        }

        return $postalmessage;
    }

    /**
     * Preserve emails within database for later accounting with webhooks
     *
     * @param Swift_Mime_SimpleMessage $swiftmessage
     * @param SendResult $response
     *
     * @return void
     */
    public function recordEmailsFromResponse(Swift_Mime_SimpleMessage $swiftmessage, SendResult $response) : void
    {
        $recipients = array();

        foreach (array('to', 'cc', 'bcc') as $field) {
            $headers = $swiftmessage->getHeaders()->get($field);

            // headers will be null if there is no CC for example
            if ($headers !== null) {
                $recipients = array_merge($recipients, $headers->getNameAddresses());
            }
        }

        $sender = $swiftmessage->getHeaders()->get('from')->getNameAddresses();

        $emailmodel = config('postal.models.email');
        foreach ($response->recipients() as $address => $message) {
            $email = new $emailmodel;

            $email->to_email = $address;
            $email->to_name = $recipients[$email->to_email];

            $email->from_email = key($sender);
            $email->from_name = $sender[$email->from_email];

            $email->subject = $swiftmessage->getSubject();

            $email->body = $swiftmessage->getBody();

            $email->postal_email_id = $response->result->message_id;
            $email->postal_id = $message->id();
            $email->postal_token = $message->token();

            $email->save();
        }
    }
}
