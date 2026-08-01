<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Degree extends SharedMaster
{
    protected $fillable = ['qualification_level_id', 'code', 'short_name', 'display_name', 'description', 'sort_order', 'is_active'];

    public function qualificationLevel(): BelongsTo
    {
        return $this->belongsTo(QualificationLevel::class);
    }
}
