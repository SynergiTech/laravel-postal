<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Notifications\Messages\MailMessage;
use SynergiTech\Postal\PostalNotificationChannel;

class PostalNotificationChannelTest extends TestCase
{
    public function testSend()
    {
        $mailerMock = $this->createMock(\Illuminate\Mail\MailManager::class);
        $markdownMock = $this->createMock(\Illuminate\Mail\Markdown::class);
        $notifyMock = $this->createMock(ExampleNotification::class);

        $notifyMock
            ->expects($this->once())
            ->method('toMail')
            ->with('test')
            ->willReturn((new MailMessage())
                ->subject('Example Notification'));

        $nc = new PostalNotificationChannel($mailerMock, $markdownMock);

        $nc->send('test', $notifyMock);
    }

    public function testGetRecipients()
    {
        $mailerMock = $this->createMock(\Illuminate\Mail\MailManager::class);
        $markdownMock = $this->createMock(\Illuminate\Mail\Markdown::class);
        $notificationMock = $this->createMock(ExampleNotification::class);
        $notifiableMock = $this->createMock(\Illuminate\Notifications\AnonymousNotifiable::class);

        $obj = new \stdClass();
        $obj->email = 'getRecipientsTest@example.com';
        $mockResponses = [
            'getRecipientsTest@example.com',
            ['getRecipientsTest@example.com'],
            [$obj],
        ];

        $notifiableMock->expects($this->exactly(count($mockResponses)))
            ->method('routeNotificationFor')
            ->with(
                PostalNotificationChannel::class,
                $notificationMock
            )
            ->will($this->onConsecutiveCalls(...$mockResponses));

        $nc = new PostalNotificationChannel($mailerMock, $markdownMock);

        $callProtectedFunction = function () use ($nc, $notifiableMock, $notificationMock) {
            $class = new \ReflectionClass($nc);
            $method = $class->getMethod('getRecipients');
            $method->setAccessible(true);
            return $method->invokeArgs($nc, [$notifiableMock, $notificationMock, null]);
        };

        $this->assertCount(3, $mockResponses);
        foreach ($mockResponses as $response) {
            $this->assertSame(['getRecipientsTest@example.com'], $callProtectedFunction());
        }
    }
}
