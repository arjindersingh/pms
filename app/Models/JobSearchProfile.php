<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobSearchProfile extends Model
{
    protected $fillable = ['user_id', 'profile_type', 'headline', 'summary', 'min_experience_years', 'max_experience_years', 'min_annual_salary', 'max_annual_salary', 'currency', 'preferred_locations', 'location_flexible', 'salary_negotiable', 'is_active'];

    protected function casts(): array
    {
        return ['preferred_locations' => 'array', 'location_flexible' => 'boolean', 'salary_negotiable' => 'boolean', 'is_active' => 'boolean', 'min_annual_salary' => 'decimal:2', 'max_annual_salary' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobTitles(): BelongsToMany
    {
        return $this->belongsToMany(JobTitle::class, 'job_search_profile_job_title');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_search_profile_skill');
    }

    public function employmentTypes(): BelongsToMany
    {
        return $this->belongsToMany(EmploymentType::class, 'job_search_profile_employment_type');
    }

    public function workModes(): BelongsToMany
    {
        return $this->belongsToMany(WorkMode::class, 'job_search_profile_work_mode');
    }

    public function qualificationLevels(): BelongsToMany
    {
        return $this->belongsToMany(QualificationLevel::class, 'job_search_profile_qualification_level');
    }

    public function organizationCategories(): BelongsToMany
    {
        return $this->belongsToMany(OrganizationCategory::class, 'job_search_profile_organization_category');
    }

    public function organizationPosts(): BelongsToMany
    {
        return $this->belongsToMany(OrganizationPost::class, 'job_search_profile_organization_post');
    }
}
