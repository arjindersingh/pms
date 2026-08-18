<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationCategory extends SharedMaster
{
    public function posts(): HasMany
    {
        return $this->hasMany(OrganizationPost::class);
    }
}
