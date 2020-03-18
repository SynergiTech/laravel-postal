<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Mail\MailManager;
use SynergiTech\Postal\PostalServiceProvider;
use SynergiTech\Postal\PostalTransport;

class PostalServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        $serviceProvider = new PostalServiceProvider($this->app);
        $mailManager = new MailManager($this->app);
        $serviceProvider->extendMailManager($mailManager);

        $driver = $mailManager->createTransport(config('mail.mailers.postal'));
        $this->assertInstanceOf(PostalTransport::class, $driver);
    }
}
