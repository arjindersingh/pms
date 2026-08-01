<?php

namespace App\Support;

use App\Models\AcademicClass;
use App\Models\Country;
use App\Models\EmploymentType;
use App\Models\Gender;
use App\Models\Language;
use App\Models\MaritalStatus;
use App\Models\ProficiencyLevel;
use App\Models\QualificationLevel;
use App\Models\Skill;
use App\Models\StudyMode;
use App\Models\WorkMode;

final class SharedMasterRegistry
{
    public const TYPES = [
        'qualification-levels' => ['label' => 'Qualification Levels', 'model' => QualificationLevel::class, 'icon' => 'bi-mortarboard'],
        'academic-classes' => ['label' => 'Academic Classes', 'model' => AcademicClass::class, 'icon' => 'bi-journal-bookmark'],
        'genders' => ['label' => 'Genders', 'model' => Gender::class, 'icon' => 'bi-person'],
        'marital-statuses' => ['label' => 'Marital Statuses', 'model' => MaritalStatus::class, 'icon' => 'bi-people'],
        'countries' => ['label' => 'Countries', 'model' => Country::class, 'icon' => 'bi-globe'],
        'languages' => ['label' => 'Languages', 'model' => Language::class, 'icon' => 'bi-translate'],
        'skills' => ['label' => 'Skills', 'model' => Skill::class, 'icon' => 'bi-tools'],
        'employment-types' => ['label' => 'Employment Types', 'model' => EmploymentType::class, 'icon' => 'bi-briefcase'],
        'work-modes' => ['label' => 'Work Modes', 'model' => WorkMode::class, 'icon' => 'bi-laptop'],
        'study-modes' => ['label' => 'Study Modes', 'model' => StudyMode::class, 'icon' => 'bi-book'],
        'proficiency-levels' => ['label' => 'Proficiency Levels', 'model' => ProficiencyLevel::class, 'icon' => 'bi-bar-chart'],
    ];

    public static function get(string $key): array
    {
        abort_unless(isset(self::TYPES[$key]), 404);

        return self::TYPES[$key];
    }
}
