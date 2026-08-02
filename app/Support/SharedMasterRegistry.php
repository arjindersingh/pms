<?php

namespace App\Support;

use App\Models\AcademicClass;
use App\Models\Country;
use App\Models\Degree;
use App\Models\EducationalInstitution;
use App\Models\EducationAuthority;
use App\Models\EmploymentType;
use App\Models\Gender;
use App\Models\Language;
use App\Models\MaritalStatus;
use App\Models\ProficiencyLevel;
use App\Models\QualificationLevel;
use App\Models\Skill;
use App\Models\StudyMode;
use App\Models\Subject;
use App\Models\WorkMode;
use App\Models\JobSector;
use App\Models\JobSpecialization;
use App\Models\JobTitle;

final class SharedMasterRegistry
{
    public const TYPES = [
        'job-sectors' => ['label' => 'Job Sectors', 'model' => JobSector::class, 'icon' => 'bi-diagram-3'],
        'job-specializations' => ['label' => 'Job Specializations', 'model' => JobSpecialization::class, 'icon' => 'bi-diagram-2', 'parent' => ['field' => 'job_sector_id', 'label' => 'Job Sector', 'model' => JobSector::class]],
        'job-titles' => ['label' => 'Job Titles', 'model' => JobTitle::class, 'icon' => 'bi-person-workspace', 'parent' => ['field' => 'job_specialization_id', 'label' => 'Job Specialization', 'model' => JobSpecialization::class]],
        'qualification-levels' => ['label' => 'Qualification Levels', 'model' => QualificationLevel::class, 'icon' => 'bi-mortarboard'],
        'degrees' => ['label' => 'Degrees / Courses', 'model' => Degree::class, 'icon' => 'bi-award', 'parent' => ['field' => 'qualification_level_id', 'label' => 'Qualification Level', 'model' => QualificationLevel::class]],
        'educational-institutions' => ['label' => 'Educational Institutions', 'model' => EducationalInstitution::class, 'icon' => 'bi-buildings'],
        'education-authorities' => ['label' => 'Boards / Universities', 'model' => EducationAuthority::class, 'icon' => 'bi-bank'],
        'academic-classes' => ['label' => 'Academic Classes', 'model' => AcademicClass::class, 'icon' => 'bi-journal-bookmark'],
        'genders' => ['label' => 'Genders', 'model' => Gender::class, 'icon' => 'bi-person'],
        'marital-statuses' => ['label' => 'Marital Statuses', 'model' => MaritalStatus::class, 'icon' => 'bi-people'],
        'countries' => ['label' => 'Countries', 'model' => Country::class, 'icon' => 'bi-globe'],
        'languages' => ['label' => 'Languages', 'model' => Language::class, 'icon' => 'bi-translate'],
        'skills' => ['label' => 'Skills', 'model' => Skill::class, 'icon' => 'bi-tools'],
        'subjects' => ['label' => 'Subjects', 'model' => Subject::class, 'icon' => 'bi-journal-text'],
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
