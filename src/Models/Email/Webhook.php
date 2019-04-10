<?php

namespace SynergiTech\Postal\Models\Email;

use Illuminate\Database\Eloquent\Model;
use SynergiTech\Postal\Models\Email;

class Webhook extends Model
{
    public $timestamps = ['created_at']; // only enable created_at

    public function email()
    {
        return $this->belongsTo(Email::class);
    }
}
