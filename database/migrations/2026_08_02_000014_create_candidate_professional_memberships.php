<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_professional_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('organization_name', 200);
            $table->string('membership_type', 150)->nullable();
            $table->string('membership_number', 150)->nullable();
            $table->date('started_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->boolean('is_lifetime')->default(false);
            $table->string('candidate_role', 150)->nullable();
            $table->string('membership_status', 30)->default('active')->index();
            $table->string('supporting_document_path', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_professional_memberships');
    }
};
