<?php

namespace SynergiTech\Postal;

use Illuminate\Mail\Transport\Transport;

use Postal\SendMessage;
use Postal\Client;
use Postal\Error;

use Swift_Attachment;
use Swift_Image;
use Swift_MimePart;
use Swift_Mime_SimpleMessage;

class PostalTransport extends Transport
{
    protected $client;

    public function __construct($domain, $key)
    {
        $this->client = new Client($domain, $key);
    }

    /**
     * Inheritdoc from Swift_Transport
     *
     * @param Swift_Mime_SimpleMessage $message
     * @param string[]                 $failedRecipients An array of failures by-reference
     *
     * @return int the number of sent messages? not sure
     */
    public function send(Swift_Mime_SimpleMessage $swiftmessage, &$failedRecipients = null)
    {
        $postalmessage = $this->swiftToPostal($swiftmessage);

        try {
            $response = $postalmessage->send();
        } catch (Error $error) {
            throw new \BadMethodCallException($error->getMessage(), $error->getCode(), $error);
        }

        // return postals response to Laravel
        $swiftmessage->postal = $response;

        // referencing Swift_Transport_SendmailTransport, this seems to be what is required
        // I don't believe this value is used in Laravel
        $count = count($postalmessage->attributes['to']) + count($postalmessage->attributes['cc']) + count($postalmessage->attributes['bcc']);
        return $count;
    }

    /**
     * Convert Swift message into a Postal sendmessage
     *
     * @param Swift_Mime_SimpleMessage $message
     *
     * @return SendMessage the converted message
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
}
