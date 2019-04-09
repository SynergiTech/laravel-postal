<?php

namespace SynergiTech\Postal;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\TransportManager;

class PostalServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/postal.php';

        // publish config
        $this->publishes([
            $configPath => config_path('postal.php'),
        ], 'config');

        // include the config file from the package if it isn't published
        $this->mergeConfigFrom($configPath, 'postal');
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->afterResolving(TransportManager::class, function (TransportManager $manager) {
            $this->extendTransportManager($manager);
        });
    }

    public function extendTransportManager(TransportManager $manager)
    {
        $manager->extend('postal', function () {
            $config = $this->app['config']->get('postal', []);
            return new PostalTransport($config);
        });
    }
}
