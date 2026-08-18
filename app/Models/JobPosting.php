<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPosting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['application_deadline' => 'date', 'published_at' => 'datetime'];
    }

    public function recruiterProfile(): BelongsTo { return $this->belongsTo(RecruiterProfile::class); }
    public function organization(): BelongsTo { return $this->belongsTo(RecruiterOrganization::class, 'recruiter_organization_id'); }
    public function organizationCategory(): BelongsTo { return $this->belongsTo(OrganizationCategory::class); }
    public function organizationPost(): BelongsTo { return $this->belongsTo(OrganizationPost::class); }
}
