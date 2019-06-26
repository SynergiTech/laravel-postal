<?php

namespace SynergiTech\Postal\Models\Email;

use Illuminate\Database\Eloquent\Model;
use SynergiTech\Postal\Models\Email;

class Webhook extends Model
{
    const UPDATED_AT = null;

    protected $table = 'email_webhooks';

    public function email()
    {
        return $this->belongsTo(Email::class);
    }
}
