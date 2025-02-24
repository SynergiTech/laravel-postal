<?php

namespace SynergiTech\Postal\Notifications;

// add this to any models that require it
// @phpstan-ignore trait.unused
trait Emailable
{
    /**
     * Get the entity's emails.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function emails()
    {
        return $this->morphMany(config('postal.models.email'), 'emailable')->orderBy('created_at', 'desc');
    }
}
