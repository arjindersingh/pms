<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CandidateProject extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
            'currently_active' => 'boolean',
            'is_featured' => 'boolean',
            'screenshots' => 'array',
            'supporting_documents' => 'array',
        ];
    }

    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class, 'project_type_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'candidate_project_skill')->withTimestamps();
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(CandidateProjectTeamMember::class);
    }
}
