<?php

namespace SynergiTech\Postal;

use SynergiTech\Postal\PostalServiceProvider;

class MailServiceProvider extends \Illuminate\Mail\MailServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->register(MailServiceProvider::class);
    }
}
