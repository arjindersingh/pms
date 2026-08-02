<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['declaration_types', 'consent_types'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('code', 40)->unique();
                $table->string('short_name', 80)->nullable();
                $table->string('display_name', 150);
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::create('candidate_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('declaration_type_id')->constrained()->restrictOnDelete();
            $table->string('declaration_version', 40);
            $table->boolean('is_accepted');
            $table->timestamp('accepted_at')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['candidate_profile_id', 'declaration_type_id']);
        });

        Schema::create('candidate_consent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consent_type_id')->constrained()->restrictOnDelete();
            $table->string('declaration_version', 40);
            $table->boolean('is_accepted');
            $table->timestamp('accepted_at')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['candidate_profile_id', 'consent_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_consent_records');
        Schema::dropIfExists('candidate_declarations');
        Schema::dropIfExists('consent_types');
        Schema::dropIfExists('declaration_types');
    }
};
