<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class State extends SharedMaster
{
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
