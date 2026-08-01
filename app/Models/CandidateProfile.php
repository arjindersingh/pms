<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CandidateProfile extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['date_of_birth' => 'date', 'available_from' => 'date', 'email_allowed' => 'boolean', 'sms_allowed' => 'boolean', 'whatsapp_allowed' => 'boolean', 'job_alerts_allowed' => 'boolean', 'is_public' => 'boolean', 'willing_to_relocate' => 'boolean', 'willing_to_travel' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(CandidateEducation::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(CandidateExperience::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)->withPivot(['proficiency_level_id', 'years_experience', 'is_primary'])->withTimestamps();
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class)->withPivot(['proficiency_level_id', 'is_native'])->withTimestamps();
    }

    public function employmentTypes(): BelongsToMany
    {
        return $this->belongsToMany(EmploymentType::class);
    }

    public function workModes(): BelongsToMany
    {
        return $this->belongsToMany(WorkMode::class);
    }
}
