<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Mail\TransportManager;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Notification;
use Postal\Client;
use SynergiTech\Postal\PostalNotificationChannel;
use SynergiTech\Postal\PostalTransport;

class FeatureTest extends TestCase
{
    public function testSendingNotificationLongForm()
    {
        $result = new \stdClass;
        $result->message_id = 'feature-test';
        $message = new \stdClass();
        $message->id = 'feature-test';
        $message->token = 'feature-test';
        $result->messages['feature-test@example.com'] = $message;

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
        Notification::route(PostalNotificationChannel::class, [$notifiable])
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

    public function testSendingNotificationShortForm()
    {
        $result = new \stdClass;
        $result->message_id = 'feature-test';
        $message = new \stdClass();
        $message->id = 'feature-test';
        $message->token = 'feature-test';
        $result->messages['feature-test@example.com'] = $message;

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
}
