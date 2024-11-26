<?php

namespace SynergiTech\Postal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Email extends Model
{
    const UPDATED_AT = null;

    /**
     * @return HasMany<Email\Webhook, $this>
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(Email\Webhook::class);
    }
}
