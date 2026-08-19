<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recruiter_candidate_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 30)->index();
            $table->string('subject');
            $table->text('message');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('meeting_location')->nullable();
            $table->string('status', 30)->default('sent')->index();
            $table->timestamps();
            $table->index(['candidate_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruiter_candidate_communications');
    }
};
