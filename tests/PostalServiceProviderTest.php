<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Mail\TransportManager;
use Illuminate\Mail\MailManager;
use SynergiTech\Postal\PostalServiceProvider;
use SynergiTech\Postal\PostalTransport;

class PostalServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        $serviceProvider = new PostalServiceProvider($this->app);

        if (class_exists(TransportManager::class)) {
            $transportManager = new TransportManager($this->app);
            $serviceProvider->extendTransportManager($transportManager);
            $driver = $transportManager->driver('postal');
        } else {
            $mailManager = new MailManager($this->app);
            $serviceProvider->extendMailManager($mailManager);
            $driver = $mailManager->createTransport(config('mail.mailers.postal'));
        }

        $this->assertInstanceOf(PostalTransport::class, $driver);
    }
}
