<?php

namespace SynergiTech\Postal;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
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

        Mail::extend('postal', function (array $config = []) {
            $config = config('postal', []);
            return new PostalTransport(new Client($config['domain'] ?? null, $config['key'] ?? null));
        });
    }
}
