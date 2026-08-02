<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_types', function (Blueprint $table) {
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

        Schema::create('candidate_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reference_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('designation', 150)->nullable();
            $table->string('organization', 200)->nullable();
            $table->string('relationship_to_candidate', 150)->nullable();
            $table->string('email')->nullable();
            $table->string('mobile', 30)->nullable();
            $table->decimal('years_known', 4, 1)->nullable();
            $table->boolean('permission_to_contact')->default(false);
            $table->boolean('consent_received')->default(false);
            $table->boolean('is_primary')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_references');
        Schema::dropIfExists('reference_types');
    }
};
