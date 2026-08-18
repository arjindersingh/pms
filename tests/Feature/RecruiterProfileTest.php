<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruiterProfileTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->seed(DatabaseSeeder::class); }

    public function test_recruiter_can_save_common_details_and_multiple_categorized_organizations(): void
    {
        $recruiter = User::where('email','recruiter@example.com')->firstOrFail();
        $this->actingAs($recruiter)->get(route('recruiter.profile.edit'))->assertOk()->assertSee('Recruiter Profile')->assertSee('Hospital / Healthcare');
        $this->put(route('recruiter.profile.update'), ['phone'=>'9876543210','work_email'=>'hr@example.com','preferred_contact_method'=>'phone','country'=>'India'])->assertSessionHasNoErrors();
        $base = ['placement_contact_name'=>'Placement Head','placement_email'=>'placement@example.com','placement_phone'=>'9876543210','address_line_1'=>'Main Road','city'=>'Chandigarh','state'=>'Chandigarh','postal_code'=>'160001','country'=>'India','is_active'=>1];
        $this->post(route('recruiter.organizations.store'), $base + ['name'=>'City College','organization_type'=>'college','is_primary'=>1])->assertSessionHasNoErrors();
        $this->post(route('recruiter.organizations.store'), $base + ['name'=>'General Hospital','organization_type'=>'hospital'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('recruiter_profiles',['user_id'=>$recruiter->id,'phone'=>'9876543210']);
        $this->assertDatabaseHas('recruiter_organizations',['name'=>'City College','organization_type'=>'college','is_primary'=>true]);
        $this->assertDatabaseHas('recruiter_organizations',['name'=>'General Hospital','organization_type'=>'hospital']);
    }

    public function test_recruiter_cannot_modify_another_recruiters_organization(): void
    {
        $owner = User::where('email','recruiter@example.com')->firstOrFail();
        $other = User::where('email','agency@example.com')->firstOrFail();
        $profile = $owner->recruiterProfile()->create(['phone'=>'1','country'=>'India']);
        $organization = $profile->organizations()->create(['name'=>'Private School','organization_type'=>'school','placement_contact_name'=>'A','placement_email'=>'a@example.com','placement_phone'=>'1','address_line_1'=>'Road','city'=>'City','state'=>'State','postal_code'=>'1','country'=>'India']);
        $this->actingAs($other)->delete(route('recruiter.organizations.destroy',$organization))->assertNotFound();
    }
}
