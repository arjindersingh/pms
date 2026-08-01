<?php

namespace App\Support;

use App\Models\AcademicClass;
use App\Models\Gender;
use App\Models\MaritalStatus;
use App\Models\QualificationLevel;

final class SharedMasterRegistry
{
    public const TYPES = [
        'qualification-levels' => ['label' => 'Qualification Levels', 'model' => QualificationLevel::class, 'icon' => 'bi-mortarboard'],
        'academic-classes' => ['label' => 'Academic Classes', 'model' => AcademicClass::class, 'icon' => 'bi-journal-bookmark'],
        'genders' => ['label' => 'Genders', 'model' => Gender::class, 'icon' => 'bi-person'],
        'marital-statuses' => ['label' => 'Marital Statuses', 'model' => MaritalStatus::class, 'icon' => 'bi-people'],
    ];

    public static function get(string $key): array
    {
        abort_unless(isset(self::TYPES[$key]), 404);

        return self::TYPES[$key];
    }
}
