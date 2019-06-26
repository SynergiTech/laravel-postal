<?php

namespace SynergiTech\Postal;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\TransportManager;
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

        // publish migrations
        $this->publishes([
            $basePath . 'migrations/email.php' => database_path(sprintf('migrations/%s_create_email_table.php', date('Y_m_d_His'))),
            $basePath . 'migrations/webhook.php' => database_path(sprintf('migrations/%s_create_email_webhook_table.php', date('Y_m_d_His'))),
        ], 'migrations');

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
            $manager->extend('postal', function () {
                $config = config('postal', []);
                return new PostalTransport(new Client($config['domain'] ?? null, $config['key'] ?? null));
            });
        });
    }
}
