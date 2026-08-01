<?php

namespace App\Enums;

enum UserCategory: string
{
    case Administrator = 'administrator';
    case Recruiter = 'recruiter';
    case Talent = 'talent';

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Administrator => 'admin.dashboard',
            self::Recruiter => 'recruiter.dashboard',
            self::Talent => 'talent.dashboard',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::Recruiter => 'Recruiter',
            self::Talent => 'Talent',
        };
    }
}
