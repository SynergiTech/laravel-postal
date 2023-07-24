<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Postal\Client;
use Postal\Send\Result;
use Postal\SendService;
use Symfony\Component\Mailer\DelayedEnvelope;
use SynergiTech\Postal\PostalNotificationChannel;
use SynergiTech\Postal\PostalTransport;

class FeatureTest extends TestCase
{
    public function testSendingNotificationOnDemand()
    {
        Mail::extend('postal', function (array $config = []) {
            $clientMock = $this->createMock(Client::class);
            $serviceMock = $this->createMock(SendService::class);

            $result = new Result([
                'message_id' => 'feature-test',
                'messages' => ['feature-test@example.com' => [
                    'id' => 123,
                    'token' => 'feature-test',
                ]],
            ]);

            $serviceMock->method('message')
                ->willReturn($result);

            $clientMock->send = $serviceMock;

            return new PostalTransport($clientMock);
        });

        $notifiable = new ExampleNotifiable();

        Notification::route(PostalNotificationChannel::class, 'feature-test@example.com')
            ->notify(new ExampleNotification($notifiable));

        $emailModel = config('postal.models.email');
        $email = $emailModel::where('to_email', 'feature-test@example.com')->first();
        $this->assertNotNull($email);

        $this->assertSame(ExampleNotifiable::class, $email->emailable_type);
        $this->assertEquals($notifiable->id, $email->emailable_id);

        $morph = $notifiable->emails()->get();
        $this->assertCount(1, $morph);
        $this->assertSame('feature-test@example.com', $morph[0]->to_email);
    }

    public function testSendingNotificationOnDemandWithAlias()
    {
        Mail::extend('postal', function (array $config = []) {
            $clientMock = $this->createMock(Client::class);
            $serviceMock = $this->createMock(SendService::class);

            $result = new Result([
                'message_id' => 'feature-test',
                'messages' => ['feature-test@example.com' => [
                    'id' => 123,
                    'token' => 'feature-test',
                ]],
            ]);

            $serviceMock->method('message')
                ->willReturn($result);

            $clientMock->send = $serviceMock;

            return new PostalTransport($clientMock);
        });

        $notifiable = new ExampleNotifiable();

        Notification::route('postal', 'feature-test@example.com')
            ->notify(new ExampleNotification($notifiable));

        $emailModel = config('postal.models.email');
        $email = $emailModel::where('to_email', 'feature-test@example.com')->first();
        $this->assertNotNull($email);

        $this->assertSame(ExampleNotifiable::class, $email->emailable_type);
        $this->assertEquals($notifiable->id, $email->emailable_id);

        $morph = $notifiable->emails()->get();
        $this->assertCount(1, $morph);
        $this->assertSame('feature-test@example.com', $morph[0]->to_email);
    }

    public function testSendingNotificationWithNotifiableTrait()
    {
        Mail::extend('postal', function (array $config = []) {
            $clientMock = $this->createMock(Client::class);
            $serviceMock = $this->createMock(SendService::class);

            $result = new Result([
                'message_id' => 'feature-test',
                'messages' => ['feature-test@example.com' => [
                    'id' => 123,
                    'token' => 'feature-test',
                ]],
            ]);

            $serviceMock->method('message')
                ->willReturn($result);

            $clientMock->send = $serviceMock;

            return new PostalTransport($clientMock);
        });

        $notifiable = new ExampleNotifiableWithRouteMethod();
        $notifiable->notify(new ExampleNotification($notifiable));

        $emailModel = config('postal.models.email');
        $email = $emailModel::where('to_email', 'feature-test@example.com')->first();
        $this->assertNotNull($email);

        $this->assertSame(ExampleNotifiableWithRouteMethod::class, $email->emailable_type);
        $this->assertEquals($notifiable->id, $email->emailable_id);

        $morph = $notifiable->emails()->get();
        $this->assertCount(1, $morph);
        $this->assertSame('feature-test@example.com', $morph[0]->to_email);
    }

    public function testSendingNotificationWithNotifiableTraitWithoutRouteMethod()
    {
        $transportMock = \Mockery::mock(PostalTransport::class);
        $transportMock->shouldReceive('send')
            ->once()
            ->withArgs(function ($email, DelayedEnvelope $envelope) {
                $recipients = $envelope->getRecipients();
                $this->assertCount(1, $recipients);

                $this->assertSame('feature-test@example.com', $recipients[0]->getAddress());
                return true;
            });

        Mail::extend('postal', function (array $config = []) use ($transportMock) {
            return $transportMock;
        });

        $notifiable = new ExampleNotifiable();
        $notifiable->notify(new ExampleNotification($notifiable));
    }
}
