<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use SynergiTech\Postal\Notifications\Emailable;

class ExampleNotifiable extends Model
{
    use Notifiable, Emailable;

    public $email = 'feature-test@example.com';

    public function __construct()
    {
        $this->id = 1234;
    }
}
