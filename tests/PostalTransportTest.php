<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use Postal\Client;
use Postal\ApiException;
use Postal\Send\Message;
use Postal\Send\Result;
use Postal\SendService;
use Symfony\Component\Mailer\Exception\TransportException;
use SynergiTech\Postal\PostalTransport;

class PostalTransportTest extends TestCase
{
    public function testSendPostalFailure(): void
    {
        // the transport converts Postal\ApiException to TransportException
        $this->expectException(TransportException::class);

        Mail::extend('postal', function (array $config = []) {
            $clientMock = $this->createMock(Client::class);
            $serviceMock = $this->createMock(SendService::class);

            $serviceMock->method('message')
                ->will($this->throwException(new ApiException()));

            $clientMock->send = $serviceMock;

            return new PostalTransport($clientMock);
        });

        Mail::to('testing@example.com')->send(new ExampleMailable());
    }

    public function testSendSuccess(): void
    {
        Mail::extend('postal', function (array $config = []) {
            $clientMock = $this->createMock(Client::class);
            $serviceMock = $this->createMock(SendService::class);

            $result = new Result([
                'message_id' => 'test',
                'messages' => ['testsendsuccess@example.com' => [
                    'id' => 123,
                    'token' => 'first',
                ]],
            ]);

            $serviceMock->method('message')
                ->willReturn($result);

            $clientMock->send = $serviceMock;

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
        Mail::extend('postal', function (array $config = []) {
            $clientMock = $this->createMock(Client::class);
            $serviceMock = $this->createMock(SendService::class);

            $result = new Result([
                'message_id' => 'caseSensitivityTest',
                'messages' => ['caseSensitivityTest@example.com' => [
                    'id' => 123,
                    'token' => 'caseSensitivityTest',
                ]],
            ]);

            $serviceMock->method('message')
                ->willReturn($result);

            $clientMock->send = $serviceMock;

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
        Mail::extend('postal', function (array $config = []) {
            $clientMock = $this->createMock(Client::class);
            $serviceMock = $this->mock(SendService::class);

            $result = new Result([
                'message_id' => 'first',
                'messages' => ['testsendsuccess@example.com' => [
                    'id' => 123,
                    'token' => 'first',
                ]],
            ]);

            $serviceMock->shouldReceive('message')
                ->withArgs(function (Message $message) {
                    $this->assertCount(1, $message->attachments);

                    return $message->attachments[0]['name'] == 'test-attachment';
                })
                ->andReturn($result);

            $clientMock->send = $serviceMock;

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
