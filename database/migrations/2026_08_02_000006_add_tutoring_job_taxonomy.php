<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('job_sectors')->updateOrInsert(
            ['code' => 'TUTORING'],
            [
                'short_name' => 'Tutoring',
                'display_name' => 'Tutoring & Tuition Services',
                'description' => 'Teaching opportunities offered by tuition centers, at learners’ homes, or online.',
                'sort_order' => 70,
                'is_active' => true,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        $sectorId = DB::table('job_sectors')->where('code', 'TUTORING')->value('id');
        $specializations = [
            ['TUITION_CENTERS', 'Tuition Centers', 'Teaching roles at coaching institutes and tuition centers.'],
            ['HOME_TUITION', 'Home Tuition', 'In-person tutoring delivered at a learner’s home.'],
            ['ONLINE_TUITION', 'Online Tuition', 'Remote tutoring delivered through online learning platforms.'],
            ['TEST_PREPARATION', 'Test Preparation', 'Coaching for entrance, competitive, and standardized examinations.'],
        ];

        foreach ($specializations as $index => [$code, $name, $description]) {
            DB::table('job_specializations')->updateOrInsert(
                ['code' => $code],
                [
                    'job_sector_id' => $sectorId,
                    'display_name' => $name,
                    'description' => $description,
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                    'deleted_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $titles = [
            ['TUITION_TEACHER', 'Tuition Teacher', 'TUITION_CENTERS'],
            ['TUITION_CENTER_FACULTY', 'Tuition Center Faculty', 'TUITION_CENTERS'],
            ['HOME_TUTOR', 'Home Tutor', 'HOME_TUITION'],
            ['PRIVATE_TUTOR', 'Private Tutor', 'HOME_TUITION'],
            ['ONLINE_TUTOR', 'Online Tutor', 'ONLINE_TUITION'],
            ['ONLINE_SUBJECT_EXPERT', 'Online Subject Expert', 'ONLINE_TUITION'],
            ['TEST_PREP_FACULTY', 'Test Preparation Faculty', 'TEST_PREPARATION'],
            ['COMPETITIVE_EXAM_TUTOR', 'Competitive Exam Tutor', 'TEST_PREPARATION'],
        ];

        foreach ($titles as $index => [$code, $name, $specializationCode]) {
            $specializationId = DB::table('job_specializations')->where('code', $specializationCode)->value('id');

            DB::table('job_titles')->updateOrInsert(
                ['code' => $code],
                [
                    'job_specialization_id' => $specializationId,
                    'display_name' => $name,
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                    'deleted_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        $specializationCodes = ['TUITION_CENTERS', 'HOME_TUITION', 'ONLINE_TUITION', 'TEST_PREPARATION'];
        $specializationIds = DB::table('job_specializations')->whereIn('code', $specializationCodes)->pluck('id');

        DB::table('job_titles')->whereIn('job_specialization_id', $specializationIds)->delete();
        DB::table('job_specializations')->whereIn('id', $specializationIds)->delete();
        DB::table('job_sectors')->where('code', 'TUTORING')->delete();
    }
};
