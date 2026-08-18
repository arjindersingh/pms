<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruiter_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('designation')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('alternate_phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('work_email')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->text('professional_summary')->nullable();
            $table->string('preferred_contact_method', 20)->default('email');
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country')->default('India');
            $table->timestamps();
        });

        Schema::create('recruiter_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruiter_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('organization_type', 40)->index();
            $table->string('other_type')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->string('organization_size', 40)->nullable();
            $table->text('description')->nullable();
            $table->string('placement_contact_name')->nullable();
            $table->string('placement_contact_designation')->nullable();
            $table->string('placement_email')->nullable();
            $table->string('placement_phone', 30)->nullable();
            $table->string('alternate_phone', 30)->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country')->default('India');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $moduleId = DB::table('portal_modules')->where('slug', 'recruitment')->value('id');
        if ($moduleId && ! DB::table('portal_menus')->where('slug', 'recruiter-profile')->exists()) {
            $menuId = DB::table('portal_menus')->insertGetId([
                'portal_module_id' => $moduleId, 'name' => 'Recruiter Profile', 'slug' => 'recruiter-profile',
                'route_name' => 'recruiter.profile.edit', 'icon' => 'bi-person-vcard', 'position' => 15,
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach (DB::table('user_roles')->where('category', 'recruiter')->pluck('id') as $roleId) {
                DB::table('portal_menu_user_role')->insert([
                    'user_role_id' => $roleId, 'portal_menu_id' => $menuId, 'can_view' => true,
                    'can_create' => true, 'can_update' => true, 'can_delete' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('portal_menus')->where('slug', 'recruiter-profile')->delete();
        Schema::dropIfExists('recruiter_organizations');
        Schema::dropIfExists('recruiter_profiles');
    }
};
