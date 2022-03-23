<?php

namespace SynergiTech\Postal;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Postal\Client;
use SynergiTech\Postal\Controllers\WebhookController;

class PostalServiceProvider extends ServiceProvider
{
    public function boot(): void
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

        $webhookRoute = config('postal.webhook.route');
        if (config('postal.enable.webhookreceiving') === true and is_string($webhookRoute)) {
            Route::post($webhookRoute, [WebhookController::class, 'process']);
        }

        Mail::extend('postal', function (array $config = []) {
            $config = config('postal', []);
            if (! is_array($config)) {
                $config = [];
            }

            return new PostalTransport(new Client($config['domain'] ?? null, $config['key'] ?? null));
        });
    }
}
