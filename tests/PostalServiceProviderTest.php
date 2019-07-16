<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Mail\TransportManager;
use SynergiTech\Postal\PostalServiceProvider;
use SynergiTech\Postal\PostalTransport;

class PostalServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        $serviceProvider = new PostalServiceProvider($this->app);
        $transportManager = new TransportManager($this->app);
        $serviceProvider->extendTransportManager($transportManager);

        $driver = $transportManager->driver('postal');
        $this->assertInstanceOf(PostalTransport::class, $driver);
    }
}
