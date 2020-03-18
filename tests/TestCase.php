<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Foundation\Testing\PendingCommand;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [\SynergiTech\Postal\PostalServiceProvider::class];
    }

    public function getKeyPair()
    {
        $fixtures = __DIR__.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR;
        return [
            'public' => trim(file_get_contents($fixtures.'key.pub')),
            'private' => file_get_contents($fixtures.'key'),
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $keyPair = $this->getKeyPair();

        $app['config']->set('postal.webhook.public_key', $keyPair['public']);
        $app['config']->set('postal.webhook.verify', true);
        $app['config']->set('postal.webhook.route', '/postal/webhook');
        $app['config']->set('mail.mailers.postal', ['transport' => 'postal']);
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    public function setUp() : void
    {
        parent::setUp();

        $this->artisan('migrate')->run();
    }
}
