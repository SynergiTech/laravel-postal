<?php

namespace SynergiTech\Postal\Models;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    const UPDATED_AT = null;

    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }
}
