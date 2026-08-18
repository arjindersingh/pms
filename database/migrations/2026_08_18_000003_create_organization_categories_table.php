<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('short_name', 80)->nullable();
            $table->string('display_name', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'sort_order']);
        });

        $now = now();
        foreach ([
            ['school', 'School'], ['college', 'College'], ['university', 'University'],
            ['hospital', 'Hospital / Healthcare'], ['company', 'Company / Corporate'],
            ['staffing_agency', 'Staffing / Placement Agency'], ['ngo', 'NGO / Non-profit'],
            ['other', 'Other'],
        ] as $position => [$code, $name]) {
            DB::table('organization_categories')->insert([
                'code' => $code, 'short_name' => $name, 'display_name' => $name,
                'sort_order' => ($position + 1) * 10, 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_categories');
    }
};
