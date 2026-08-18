<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruiterOrganization extends Model
{
    public const TYPES = [
        'school' => 'School', 'college' => 'College', 'university' => 'University',
        'hospital' => 'Hospital / Healthcare', 'company' => 'Company / Corporate',
        'staffing_agency' => 'Staffing / Placement Agency', 'ngo' => 'NGO / Non-profit', 'other' => 'Other',
    ];

    protected $guarded = [];
    protected function casts(): array { return ['is_primary' => 'boolean', 'is_active' => 'boolean']; }
    public function recruiterProfile(): BelongsTo { return $this->belongsTo(RecruiterProfile::class); }
    public function getTypeLabelAttribute(): string
    {
        return OrganizationCategory::withTrashed()->where('code', $this->organization_type)->value('display_name')
            ?? self::TYPES[$this->organization_type]
            ?? $this->other_type
            ?? 'Other';
    }
}
