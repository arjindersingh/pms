<?php
namespace Tests\Feature;
use App\Models\CompanyProfile;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->seed(DatabaseSeeder::class); }
    public function test_admin_can_update_company_branding_used_across_public_and_portal_views(): void
    {
        $admin=User::where('email','admin@example.com')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.company.update'),['display_name'=>'Acme Careers','legal_name'=>'Acme Careers Limited','tagline'=>'People. Potential. Progress.','email'=>'hello@acme.test','website'=>'https://acme.test','promotion_enabled'=>1])->assertRedirect();
        $this->assertDatabaseHas('company_profiles',['display_name'=>'Acme Careers','legal_name'=>'Acme Careers Limited']);
        $this->get(route('home'))->assertOk()->assertSee('Acme Careers')->assertSee('People. Potential. Progress.');
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Acme Careers')->assertSee('Acme Careers Limited');
    }
    public function test_company_profile_page_is_available_to_super_admin(): void
    {
        $this->actingAs(User::where('email','admin@example.com')->firstOrFail())->get(route('admin.company.edit'))->assertOk()->assertSee('Company profile')->assertSee(CompanyProfile::current()->display_name);
    }
}
