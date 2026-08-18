<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruiter_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recruiter_organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('organization_post_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 150);
            $table->string('employment_type', 40)->nullable();
            $table->string('work_mode', 40)->nullable();
            $table->string('location', 180)->nullable();
            $table->unsignedInteger('vacancies')->default(1);
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->date('application_deadline')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        DB::table('portal_menus')->where('slug', 'hiring-workspace')->update([
            'name' => 'Talent Acquisition Hub', 'icon' => 'bi-rocket-takeoff', 'updated_at' => now(),
        ]);
        DB::table('portal_menus')->where('slug', 'job-postings')->update([
            'route_name' => 'recruiter.job-postings.index', 'updated_at' => now(),
        ]);
        DB::table('portal_menus')->where('slug', 'create-job')->update([
            'route_name' => 'recruiter.job-postings.create', 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('portal_menus')->where('slug', 'hiring-workspace')->update([
            'name' => 'Hiring Workspace', 'icon' => 'bi-briefcase', 'updated_at' => now(),
        ]);
        DB::table('portal_menus')->whereIn('slug', ['job-postings', 'create-job'])->update(['route_name' => null, 'updated_at' => now()]);
        Schema::dropIfExists('job_postings');
    }
};
