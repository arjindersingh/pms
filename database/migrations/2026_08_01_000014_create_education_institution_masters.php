<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['educational_institutions', 'education_authorities'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $table->string('code', 60)->unique();
                $table->string('short_name', 100)->nullable();
                $table->string('display_name', 200);
                if ($name === 'education_authorities') {
                    $table->string('authority_type', 20)->default('university')->index();
                }
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }
        Schema::table('candidate_educations', function (Blueprint $table) {
            $table->foreignId('educational_institution_id')->nullable()->after('specialization')->constrained()->nullOnDelete();
            $table->foreignId('education_authority_id')->nullable()->after('institution_name')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('candidate_educations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('education_authority_id');
            $table->dropConstrainedForeignId('educational_institution_id');
        });
        Schema::dropIfExists('education_authorities');
        Schema::dropIfExists('educational_institutions');
    }
};
