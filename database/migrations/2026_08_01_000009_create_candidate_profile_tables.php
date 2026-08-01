<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['countries', 'languages', 'skills', 'employment_types', 'work_modes', 'study_modes', 'proficiency_levels'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id(); $table->string('code', 40)->unique(); $table->string('short_name', 80)->nullable();
                $table->string('display_name', 150); $table->text('description')->nullable(); $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index(); $table->timestamps(); $table->softDeletes();
            });
        }

        Schema::create('candidate_profiles', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $table->string('profile_code')->unique();
            $table->string('first_name')->nullable(); $table->string('middle_name')->nullable(); $table->string('last_name')->nullable();
            $table->date('date_of_birth')->nullable(); $table->foreignId('gender_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marital_status_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('nationality_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('headline')->nullable(); $table->text('career_objective')->nullable(); $table->text('professional_summary')->nullable();
            $table->string('mobile', 30)->nullable(); $table->string('whatsapp', 30)->nullable(); $table->string('alternate_email')->nullable();
            $table->string('linkedin_url')->nullable(); $table->string('portfolio_url')->nullable();
            $table->string('address_line_1')->nullable(); $table->string('address_line_2')->nullable(); $table->string('city')->nullable();
            $table->string('state')->nullable(); $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete(); $table->string('postal_code', 20)->nullable();
            $table->boolean('email_allowed')->default(true); $table->boolean('sms_allowed')->default(true); $table->boolean('whatsapp_allowed')->default(true);
            $table->boolean('job_alerts_allowed')->default(true); $table->boolean('is_public')->default(false);
            $table->string('availability_status')->nullable(); $table->date('available_from')->nullable(); $table->boolean('willing_to_relocate')->default(false);
            $table->boolean('willing_to_travel')->default(false); $table->decimal('expected_salary_min', 12, 2)->nullable(); $table->decimal('expected_salary_max', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_educations', function (Blueprint $table) {
            $table->id(); $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('qualification_level_id')->constrained()->restrictOnDelete(); $table->string('degree_name'); $table->string('specialization')->nullable();
            $table->string('institution_name'); $table->string('board_university')->nullable(); $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('study_mode_id')->nullable()->constrained()->nullOnDelete(); $table->unsignedSmallInteger('start_year')->nullable(); $table->unsignedSmallInteger('passing_year')->nullable();
            $table->boolean('currently_studying')->default(false); $table->string('result')->nullable(); $table->timestamps();
        });

        Schema::create('candidate_experiences', function (Blueprint $table) {
            $table->id(); $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete(); $table->string('organization_name'); $table->string('designation');
            $table->foreignId('employment_type_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('work_mode_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete(); $table->string('city')->nullable(); $table->date('started_on'); $table->date('ended_on')->nullable();
            $table->boolean('currently_working')->default(false); $table->text('description')->nullable(); $table->timestamps();
        });

        Schema::create('candidate_profile_skill', function (Blueprint $table) {
            $table->id(); $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete(); $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proficiency_level_id')->nullable()->constrained()->nullOnDelete(); $table->decimal('years_experience', 4, 1)->nullable(); $table->boolean('is_primary')->default(false);
            $table->timestamps(); $table->unique(['candidate_profile_id', 'skill_id']);
        });
        Schema::create('candidate_profile_language', function (Blueprint $table) {
            $table->id(); $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete(); $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proficiency_level_id')->nullable()->constrained()->nullOnDelete(); $table->boolean('is_native')->default(false);
            $table->timestamps(); $table->unique(['candidate_profile_id', 'language_id']);
        });
        Schema::create('candidate_profile_employment_type', function (Blueprint $table) { $table->id(); $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete(); $table->foreignId('employment_type_id')->constrained()->cascadeOnDelete(); $table->unique(['candidate_profile_id', 'employment_type_id']); });
        Schema::create('candidate_profile_work_mode', function (Blueprint $table) { $table->id(); $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete(); $table->foreignId('work_mode_id')->constrained()->cascadeOnDelete(); $table->unique(['candidate_profile_id', 'work_mode_id']); });
    }

    public function down(): void
    {
        foreach (['candidate_profile_work_mode','candidate_profile_employment_type','candidate_profile_language','candidate_profile_skill','candidate_experiences','candidate_educations','candidate_profiles','proficiency_levels','study_modes','work_modes','employment_types','skills','languages','countries'] as $name) Schema::dropIfExists($name);
    }
};
