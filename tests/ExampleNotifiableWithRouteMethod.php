<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use SynergiTech\Postal\Notifications\Emailable;

class ExampleNotifiableWithRouteMethod extends Model
{
    use Notifiable, Emailable;

    public $email = 'feature-test@example.com';

    public function __construct()
    {
        $this->id = 1234;
    }

    public function routeNotificationForPostal($notification)
    {
        return $this->email;
    }
}
