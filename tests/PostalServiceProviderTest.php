<?php

namespace SynergiTech\Postal\Tests;

use SynergiTech\Postal\PostalTransport;

class PostalServiceProviderTest extends TestCase
{
    public function testBoot()
    {
        // assert the service provider did boot and extend the mail

        $driver = app('mail.manager')
            ->createSymfonyTransport(config('mail.mailers.postal'));

        $this->assertInstanceOf(PostalTransport::class, $driver);

        $this->assertSame('postal', (string) $driver);
    }
}
