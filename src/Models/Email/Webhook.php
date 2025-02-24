<?php

namespace SynergiTech\Postal\Models\Email;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SynergiTech\Postal\Models\Email;

class Webhook extends Model
{
    const UPDATED_AT = null;

    protected $table = 'email_webhooks';

    /**
     * @return BelongsTo<Email, $this>
     */
    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }
}
