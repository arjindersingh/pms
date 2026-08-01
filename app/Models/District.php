<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends SharedMaster
{
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
