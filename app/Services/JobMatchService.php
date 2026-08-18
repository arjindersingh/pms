<?php

namespace App\Services;

use App\Models\JobSearchProfile;
use Illuminate\Support\Collection;

class JobMatchService
{
    public function matches(JobSearchProfile $source, int $limit = 20): Collection
    {
        $source->loadMissing(['jobTitles', 'skills', 'employmentTypes', 'workModes', 'qualificationLevels', 'organizationCategories', 'organizationPosts']);

        return JobSearchProfile::with(['user.userType', 'jobTitles', 'skills', 'employmentTypes', 'workModes', 'qualificationLevels', 'organizationCategories', 'organizationPosts'])->where('profile_type', $source->profile_type === 'talent' ? 'recruiter' : 'talent')->where('is_active', true)->get()->map(fn ($target) => ['profile' => $target, 'score' => $this->score($source, $target), 'reasons' => $this->reasons($source, $target)])->filter(fn ($m) => $m['score'] > 0)->sortByDesc('score')->take($limit)->values();
    }

    public function score(JobSearchProfile $a, JobSearchProfile $b): int
    {
        return min(100, $this->overlap($a->jobTitles, $b->jobTitles) * 30 + $this->overlap($a->skills, $b->skills) * 15 + $this->overlap($a->employmentTypes, $b->employmentTypes) * 10 + $this->overlap($a->workModes, $b->workModes) * 10 + $this->overlap($a->qualificationLevels, $b->qualificationLevels) * 10 + $this->overlap($a->organizationCategories, $b->organizationCategories) * 5 + $this->overlap($a->organizationPosts, $b->organizationPosts) * 10 + ($this->rangeOverlaps($a->min_experience_years, $a->max_experience_years, $b->min_experience_years, $b->max_experience_years) ? 5 : 0) + ($this->salaryOverlaps($a, $b) ? 5 : 0));
    }

    private function overlap($a, $b): int
    {
        return $a->pluck('id')->intersect($b->pluck('id'))->isNotEmpty() ? 1 : 0;
    }

    private function rangeOverlaps($aMin, $aMax, $bMin, $bMax): bool
    {
        return ($aMin ?? 0) <= ($bMax ?? PHP_INT_MAX) && ($bMin ?? 0) <= ($aMax ?? PHP_INT_MAX);
    }

    private function salaryOverlaps($a, $b): bool
    {
        return $a->currency === $b->currency && $this->rangeOverlaps($a->min_annual_salary, $a->max_annual_salary, $b->min_annual_salary, $b->max_annual_salary);
    }

    private function reasons($a, $b): array
    {
        return collect([['Job title', $a->jobTitles->pluck('id')->intersect($b->jobTitles->pluck('id'))->isNotEmpty()], ['Skills', $a->skills->pluck('id')->intersect($b->skills->pluck('id'))->isNotEmpty()], ['Work mode', $a->workModes->pluck('id')->intersect($b->workModes->pluck('id'))->isNotEmpty()], ['Organisation type', $a->organizationCategories->pluck('id')->intersect($b->organizationCategories->pluck('id'))->isNotEmpty()], ['Organisation post', $a->organizationPosts->pluck('id')->intersect($b->organizationPosts->pluck('id'))->isNotEmpty()], ['Experience', $this->rangeOverlaps($a->min_experience_years, $a->max_experience_years, $b->min_experience_years, $b->max_experience_years)], ['Salary', $this->salaryOverlaps($a, $b)]])->filter(fn ($r) => $r[1])->pluck(0)->all();
    }
}
