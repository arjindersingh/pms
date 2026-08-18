<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_category_id')->constrained()->restrictOnDelete();
            $table->string('code', 40);
            $table->string('short_name', 80)->nullable();
            $table->string('display_name', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['organization_category_id', 'code']);
            $table->index(['organization_category_id', 'is_active', 'sort_order'], 'organization_posts_category_active_order_index');
        });

        $categories = DB::table('organization_categories')->pluck('id', 'code');
        $now = now();
        foreach ([
            ['school', 'teacher', 'Teacher'],
            ['school', 'assistant_teacher', 'Assistant Teacher'],
            ['school', 'principal', 'Principal'],
            ['hospital', 'doctor', 'Doctor'],
            ['hospital', 'nurse', 'Nurse'],
            ['hospital', 'medical_assistant', 'Medical Assistant'],
        ] as $position => [$categoryCode, $code, $name]) {
            if (! isset($categories[$categoryCode])) {
                continue;
            }
            DB::table('organization_posts')->insert([
                'organization_category_id' => $categories[$categoryCode],
                'code' => $code,
                'short_name' => $name,
                'display_name' => $name,
                'sort_order' => ($position + 1) * 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_posts');
    }
};
