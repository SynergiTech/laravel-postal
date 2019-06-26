<?php

namespace SynergiTech\Postal\Models;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    public $timestamps = ['created_at']; // only enable created_at

    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }
}
