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
        return ['date_of_birth' => 'date', 'available_from' => 'date', 'photo_updated_at' => 'datetime', 'email_allowed' => 'boolean', 'sms_allowed' => 'boolean', 'whatsapp_allowed' => 'boolean', 'job_alerts_allowed' => 'boolean', 'is_public' => 'boolean', 'willing_to_relocate' => 'boolean', 'willing_to_travel' => 'boolean'];
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

    public function publications(): HasMany
    {
        return $this->hasMany(CandidatePublication::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(CandidateProject::class);
    }

    public function recognitions(): HasMany
    {
        return $this->hasMany(CandidateRecognition::class);
    }

    public function professionalMemberships(): HasMany
    {
        return $this->hasMany(CandidateProfessionalMembership::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(CandidateReference::class);
    }

    public function socialProfiles(): HasMany
    {
        return $this->hasMany(CandidateSocialProfile::class);
    }

    public function declarations(): HasMany
    {
        return $this->hasMany(CandidateDeclaration::class);
    }

    public function consentRecords(): HasMany
    {
        return $this->hasMany(CandidateConsentRecord::class);
    }

    public function awards(): HasMany
    {
        return $this->recognitions()->where('kind', 'award');
    }

    public function honours(): HasMany
    {
        return $this->recognitions()->where('kind', 'honour');
    }

    public function scholarships(): HasMany
    {
        return $this->recognitions()->where('kind', 'scholarship');
    }

    public function competitionResults(): HasMany
    {
        return $this->recognitions()->where('kind', 'competition');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)->withPivot(['skill_group_id', 'proficiency_level_id', 'years_experience', 'is_primary', 'remarks'])->withTimestamps();
    }

    public function talents(): BelongsToMany
    {
        return $this->belongsToMany(Talent::class)->withPivot(['talent_category_id', 'proficiency_level_id', 'years_practised', 'achievements', 'evidence_url', 'is_featured'])->withTimestamps();
    }

    public function hobbies(): BelongsToMany
    {
        return $this->belongsToMany(Hobby::class)->withPivot(['hobby_category_id', 'interest_level_id', 'years_active', 'description'])->withTimestamps();
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
