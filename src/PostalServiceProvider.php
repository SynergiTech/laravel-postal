<?php

namespace SynergiTech\Postal;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\TransportManager;

class PostalServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->afterResolving(TransportManager::class, function (TransportManager $manager) {
            $this->extendTransportManager($manager);
        });
    }

    public function extendTransportManager(TransportManager $manager)
    {
        $manager->extend('postal', function () {
            $config = $this->app['config']->get('services.postal', []);
            return new PostalTransport($config['domain'], $config['key']);
        });
    }
}
