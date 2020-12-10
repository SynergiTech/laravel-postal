<?php

namespace SynergiTech\Postal\Tests;

use Postal\Client;
use Postal\Error;
use Postal\SendResult;
use SynergiTech\Postal\PostalTransport;
use Illuminate\Mail\TransportManager;
use Illuminate\Mail\MailManager;
use SynergiTech\Postal\PostalNotificationChannel;
use Illuminate\Support\Facades\Notification;

class PostalTransportTest extends TestCase
{
    public function testSendPostalFailure()
    {
        $this->expectException(\BadMethodCallException::class);

        $clientMock = $this->createMock(Client::class);
        $clientMock
            ->method('makeRequest')
            ->will($this->throwException(new Error()));

        $message = new \Swift_Message();
        $transport = new PostalTransport($clientMock);
        $transport->send($message);
    }

    public function testSendSuccess()
    {
        $clientMock = $this->createMock(Client::class);
        $result = new \stdClass;
        $result->message_id = 'test';
        $message = new \stdClass();
        $message->id = 'first';
        $message->token = 'first';
        $result->messages['testsendsuccess@example.com'] = $message;

        $sendResult = new SendResult($clientMock, (object)$result);
        $clientMock->method('makeRequest')->willReturn((object)$result);

        $message = new \Swift_Message();
        $message->setFrom(['john@doe.com' => 'John Doe']);
        $message->setTo(['testsendsuccess@example.com']);

        $transport = new PostalTransport($clientMock);
        $recipients = $transport->send($message);

        $this->assertSame(1, $recipients);

        $emailModel = config('postal.models.email');
        $this->assertNotNull($emailModel::where('to_email', 'testsendsuccess@example.com')->first());
    }

    public function testSwiftToPostal()
    {
        $clientMock = $this->createMock(Client::class);

        $swift = (new \Swift_Message())
            ->setSubject('Subject')
            ->setFrom(['john@doe.com' => 'John Doe'])
            ->setTo(['receiver@example.com', 'other@example.com' => 'A name'])
            ->setReplyTo(['person@example.com'])
            ->setBody('Body')
            ->addPart('<span>HTML Body</span>', 'text/html')
            ->attach(\Swift_Attachment::fromPath(__DIR__.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'test-attachment'));
        $swift->embed(\Swift_Image::fromPath(__DIR__.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'test-image'));

        $transport = new PostalTransport($clientMock);

        $postal = $transport->swiftToPostal($swift);

        $this->assertSame($swift->getSubject(), $postal->attributes['subject']);
        $this->assertSame($swift->getBody(), $postal->attributes['plain_body']);
        $this->assertSame('<span>HTML Body</span>', $postal->attributes['html_body']);
        $this->assertSame(['receiver@example.com', 'A name <other@example.com>'], $postal->attributes['to']);
        $this->assertSame('John Doe <john@doe.com>', $postal->attributes['from']);
        $this->assertSame('person@example.com', $postal->attributes['reply_to']);
        $this->assertCount(2, $postal->attributes['attachments']);
    }

    public function testSwiftToPostalQuirk()
    {
        $clientMock = $this->createMock(Client::class);

        $swift = (new \Swift_Message())
            ->addPart('Body', 'text/plain')
            ->setBody('<span>HTML Body</span>', 'text/html');

        $transport = new PostalTransport($clientMock);

        $postal = $transport->swiftToPostal($swift);

        $this->assertSame($swift->getBody(), $postal->attributes['html_body']);
        $this->assertSame('Body', $postal->attributes['plain_body']);
    }

    public function testPostalCaseSensitivity()
    {
        $result = new \stdClass;
        $result->message_id = 'caseSensitivityTest';
        $message = new \stdClass();
        $message->id = 'caseSensitivityTest';
        $message->token = 'caseSensitivityTest';
        $result->messages['caseSensitivityTest@example.com'] = $message;

        $clientMock = $this->createMock(Client::class);
        $clientMock
            ->method('makeRequest')
            ->willReturn($result);

        $this->app->afterResolving(TransportManager::class, function (TransportManager $manager) use ($clientMock) {
                $manager->extend('postal', function () use ($clientMock) {
                return new PostalTransport($clientMock);
            });
        });

        $this->app->afterResolving(MailManager::class, function (MailManager $manager) use ($clientMock) {
            $manager->extend('postal', function () use ($clientMock) {
                return new PostalTransport($clientMock);
            });
        });

        $notifiable = new ExampleNotifiable();
        Notification::route(PostalNotificationChannel::class, ['caseSensitivityTest@example.com'])
            ->notify(new ExampleNotification($notifiable));

        $emailModel = config('postal.models.email');
        $email = $emailModel::where('to_email', 'caseSensitivityTest@example.com')->first();
        $this->assertNotNull($email);
        $this->assertSame('caseSensitivityTest@example.com', $email->to_email);
    }
}
