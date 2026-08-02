<?php

namespace App\Support;

use App\Models\AcademicClass;
use App\Models\ConsentType;
use App\Models\Country;
use App\Models\DeclarationType;
use App\Models\Degree;
use App\Models\EducationalInstitution;
use App\Models\EducationAuthority;
use App\Models\EmploymentType;
use App\Models\Gender;
use App\Models\Hobby;
use App\Models\HobbyCategory;
use App\Models\InterestLevel;
use App\Models\JobSector;
use App\Models\JobSpecialization;
use App\Models\JobTitle;
use App\Models\Language;
use App\Models\MaritalStatus;
use App\Models\ProficiencyLevel;
use App\Models\ProjectType;
use App\Models\PublicationMode;
use App\Models\PublicationType;
use App\Models\QualificationLevel;
use App\Models\RecognitionLevel;
use App\Models\ReferenceType;
use App\Models\Skill;
use App\Models\SkillGroup;
use App\Models\SocialPlatform;
use App\Models\StudyMode;
use App\Models\Subject;
use App\Models\Talent;
use App\Models\TalentCategory;
use App\Models\WorkMode;

final class SharedMasterRegistry
{
    public const TYPES = [
        'declaration-types' => ['label' => 'Declaration Types', 'model' => DeclarationType::class, 'icon' => 'bi-file-earmark-check'],
        'consent-types' => ['label' => 'Consent Types', 'model' => ConsentType::class, 'icon' => 'bi-shield-check'],
        'job-sectors' => ['label' => 'Job Sectors', 'model' => JobSector::class, 'icon' => 'bi-diagram-3'],
        'job-specializations' => ['label' => 'Job Specializations', 'model' => JobSpecialization::class, 'icon' => 'bi-diagram-2', 'parent' => ['field' => 'job_sector_id', 'label' => 'Job Sector', 'model' => JobSector::class]],
        'job-titles' => ['label' => 'Job Titles', 'model' => JobTitle::class, 'icon' => 'bi-person-workspace', 'parent' => ['field' => 'job_specialization_id', 'label' => 'Job Specialization', 'model' => JobSpecialization::class]],
        'publication-types' => ['label' => 'Publication Types', 'model' => PublicationType::class, 'icon' => 'bi-journal-richtext'],
        'publication-modes' => ['label' => 'Publication Modes', 'model' => PublicationMode::class, 'icon' => 'bi-globe2'],
        'project-types' => ['label' => 'Project Types', 'model' => ProjectType::class, 'icon' => 'bi-kanban'],
        'recognition-levels' => ['label' => 'Award & Achievement Levels', 'model' => RecognitionLevel::class, 'icon' => 'bi-trophy'],
        'reference-types' => ['label' => 'Reference Types', 'model' => ReferenceType::class, 'icon' => 'bi-person-check'],
        'social-platforms' => ['label' => 'Social Platforms', 'model' => SocialPlatform::class, 'icon' => 'bi-share'],
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
        'skill-groups' => ['label' => 'Skill Groups', 'model' => SkillGroup::class, 'icon' => 'bi-tags'],
        'talent-categories' => ['label' => 'Talent Categories', 'model' => TalentCategory::class, 'icon' => 'bi-collection'],
        'talents' => ['label' => 'Talents', 'model' => Talent::class, 'icon' => 'bi-stars'],
        'hobby-categories' => ['label' => 'Hobby Categories', 'model' => HobbyCategory::class, 'icon' => 'bi-collection'],
        'interest-levels' => ['label' => 'Interest Levels', 'model' => InterestLevel::class, 'icon' => 'bi-bar-chart-steps'],
        'hobbies' => ['label' => 'Hobbies', 'model' => Hobby::class, 'icon' => 'bi-heart'],
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
