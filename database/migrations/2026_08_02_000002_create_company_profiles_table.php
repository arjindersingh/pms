<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id(); $table->string('display_name')->default('PlaceFlow'); $table->string('legal_name')->nullable();
            $table->string('tagline')->nullable(); $table->text('short_description')->nullable(); $table->longText('about')->nullable();
            $table->text('mission')->nullable(); $table->text('vision')->nullable(); $table->text('values')->nullable();
            $table->string('registration_number')->nullable(); $table->string('tax_number')->nullable(); $table->date('founded_on')->nullable();
            $table->string('industry')->nullable(); $table->string('company_size')->nullable();
            $table->string('email')->nullable(); $table->string('support_email')->nullable(); $table->string('phone')->nullable(); $table->string('whatsapp')->nullable(); $table->string('website')->nullable();
            $table->string('address_line_1')->nullable(); $table->string('address_line_2')->nullable(); $table->string('city')->nullable();
            $table->string('state')->nullable(); $table->string('postal_code')->nullable(); $table->string('country')->nullable();
            $table->string('logo_path')->nullable(); $table->string('logo_dark_path')->nullable(); $table->string('favicon_path')->nullable(); $table->string('cover_path')->nullable();
            $table->json('social_links')->nullable(); $table->string('meta_title')->nullable(); $table->text('meta_description')->nullable(); $table->string('meta_keywords')->nullable();
            $table->boolean('promotion_enabled')->default(true); $table->string('promotion_heading')->nullable(); $table->text('promotion_text')->nullable(); $table->string('promotion_cta_label')->nullable(); $table->string('promotion_cta_url')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });
        DB::table('company_profiles')->insert(['display_name'=>'PlaceFlow','tagline'=>'Where talent meets opportunity','short_description'=>'A modern placement portal connecting ambitious talent with exceptional recruiters.','promotion_enabled'=>true,'promotion_heading'=>'Ready to find where you belong?','promotion_text'=>'Join a placement community built around potential, progress, and possibility.','created_at'=>now(),'updated_at'=>now()]);
        $moduleId=DB::table('portal_modules')->where('slug','administration')->value('id');
        if($moduleId){$menuId=DB::table('portal_menus')->insertGetId(['portal_module_id'=>$moduleId,'parent_id'=>null,'name'=>'Company Profile','slug'=>'company-profile','route_name'=>'admin.company.edit','icon'=>'bi-buildings','position'=>15,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);foreach(DB::table('user_roles')->where('category','administrator')->get() as $role)DB::table('portal_menu_user_role')->insert(['user_role_id'=>$role->id,'portal_menu_id'=>$menuId,'can_view'=>true,'can_create'=>false,'can_update'=>!str_contains($role->slug,'auditor'),'can_delete'=>false,'created_at'=>now(),'updated_at'=>now()]);}
    }
    public function down(): void { DB::table('portal_menus')->where('slug','company-profile')->delete(); Schema::dropIfExists('company_profiles'); }
};
