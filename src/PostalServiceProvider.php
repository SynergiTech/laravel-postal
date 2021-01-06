<?php

namespace SynergiTech\Postal;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\TransportManager;
use Illuminate\Mail\MailManager;
use Postal\Client;

class PostalServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $basePath = __DIR__ . '/../';
        $configPath = $basePath . 'config/postal.php';

        // publish config
        $this->publishes([
            $configPath => config_path('postal.php'),
        ], 'config');

        $this->loadMigrationsFrom($basePath . 'migrations');

        // include the config file from the package if it isn't published
        $this->mergeConfigFrom($configPath, 'postal');

        if (config('postal.enable.webhookreceiving') === true) {
            \Route::post(config('postal.webhook.route'), 'SynergiTech\Postal\Controllers\WebhookController@process');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->afterResolving(TransportManager::class, function (TransportManager $manager) {
            $this->extendTransportManager($manager);
        });

        $this->app->afterResolving(MailManager::class, function (MailManager $manager) {
            $this->extendMailManager($manager);
        });
    }

    public function extendTransportManager(TransportManager $manager)
    {
        $manager->extend('postal', function () {
            $config = config('postal', []);
            return new PostalTransport(new Client($config['domain'] ?? null, $config['key'] ?? null));
        });
    }

    public function extendMailManager(MailManager $manager)
    {
        $manager->extend('postal', function () {
            $config = config('postal', []);
            return new PostalTransport(new Client($config['domain'] ?? null, $config['key'] ?? null));
        });
    }
}
