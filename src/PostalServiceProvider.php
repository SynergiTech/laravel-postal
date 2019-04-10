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
        $basePath = __DIR__ . '/../';
        $configPath = $basePath . 'config/postal.php';

        // publish config
        $this->publishes([
            $configPath => config_path('postal.php'),
        ], 'config');

        // publish migrations
        $this->publishes([
            $basePath . 'migrations/email.php' => database_path(sprintf('%s_create_email_table.php', date('Y_m_d_His'))),
            $basePath . 'migrations/webhook.php' => database_path(sprintf('%s_create_webhook_table.php', date('Y_m_d_His'))),
        ], 'migrations');

        // include the config file from the package if it isn't published
        $this->mergeConfigFrom($configPath, 'postal');

        \Route::post('/postal/webhook', 'SynergiTech\Postal\Controllers\WebhookController@process');
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
