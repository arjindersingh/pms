<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationPost extends SharedMaster
{
    protected $fillable = [
        'organization_category_id',
        'code',
        'short_name',
        'display_name',
        'description',
        'sort_order',
        'is_active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(OrganizationCategory::class, 'organization_category_id');
    }
}
