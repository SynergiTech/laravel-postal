<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use Postal\Client;
use Postal\Error;
use Symfony\Component\Mailer\Exception\TransportException;
use SynergiTech\Postal\PostalTransport;

class PostalTransportTest extends TestCase
{
    public function testSendPostalFailure(): void
    {
        // requests requires a URL
        config(['postal.domain' => 'http://example.com']);

        // the transport converts Postal\Error to TransportException
        $this->expectException(TransportException::class);

        $clientMock = $this->createMock(Client::class);
        $clientMock
            ->method('makeRequest')
            ->will($this->throwException(new Error()));

        Mail::extend('postal', function (array $config = []) use ($clientMock) {
            return new PostalTransport($clientMock);
        });

        Mail::to('testing@example.com')->send(new ExampleMailable());
    }

    public function testSendSuccess(): void
    {
        // requests requires a URL
        config(['postal.domain' => 'http://example.com']);

        $clientMock = $this->createMock(Client::class);
        $result = new \stdClass;
        $result->message_id = 'test';
        $message = new \stdClass();
        $message->id = 'first';
        $message->token = 'first';
        $result->messages['testsendsuccess@example.com'] = $message;

        $clientMock->method('makeRequest')->willReturn((object)$result);

        Mail::extend('postal', function (array $config = []) use ($clientMock) {
            return new PostalTransport($clientMock);
        });

        Mail::to('testsendsuccess@example.com')->send(new ExampleMailable(
            fromEmail: 'john@doe.com'
        ));

        $emailModel = config('postal.models.email');
        $this->assertNotNull(
            $emailModel::where('to_email', 'testsendsuccess@example.com')
                ->where('from_email', 'john@doe.com')
                ->first()
        );
    }

    public function testPostalCaseSensitivity(): void
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

        Mail::extend('postal', function (array $config = []) use ($clientMock) {
            return new PostalTransport($clientMock);
        });

        Mail::to('caseSensitivityTest@example.com')->send(new ExampleMailable(
            fromEmail: 'FancyName@example.com'
        ));

        $emailModel = config('postal.models.email');
        $email = $emailModel::where('to_email', 'caseSensitivityTest@example.com')->first();
        $this->assertNotNull($email);
        $this->assertSame('caseSensitivityTest@example.com', $email->to_email);
        $this->assertSame('FancyName@example.com', $email->from_email);
    }

    public function testAttachments(): void
    {
        // requests requires a URL
        config(['postal.domain' => 'http://example.com']);

        $clientMock = $this->mock(Client::class, function (MockInterface $mock) {
            $message = new \stdClass();
            $message->id = 'first';
            $message->token = 'first';

            $result = new \stdClass;
            $result->message_id = 'test';
            $result->messages['testsendsuccess@example.com'] = $message;

            $mock->shouldReceive('makeRequest')
                ->withArgs(function ($controller, $action, $parameters) {
                    $this->assertCount(1, $parameters['attachments']);
                    $this->assertSame('test-attachment', $parameters['attachments'][0]['name']);

                    return $controller == 'send' && $action == 'message';
                })
                ->andReturn((object)$result);
        });

        Mail::extend('postal', function (array $config = []) use ($clientMock) {
            return new PostalTransport($clientMock);
        });

        Mail::to('testsendsuccess@example.com')->send(new ExampleMailableWithAttachments());

        $emailModel = config('postal.models.email');
        $this->assertNotNull(
            $emailModel::where('to_email', 'testsendsuccess@example.com')
                ->first()
        );
    }
}
