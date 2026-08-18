<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends SharedMaster
{
    protected $fillable = ['state_id', 'code', 'short_name', 'display_name', 'description', 'sort_order', 'is_active'];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
