<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CandidateEducation extends Model
{
    protected $table = 'candidate_educations';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['currently_studying' => 'boolean'];
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)->withTimestamps();
    }

    public function qualificationLevel(): BelongsTo
    {
        return $this->belongsTo(QualificationLevel::class);
    }

    public function degree(): BelongsTo
    {
        return $this->belongsTo(Degree::class);
    }
}
