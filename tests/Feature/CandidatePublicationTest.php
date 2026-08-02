<?php
namespace Tests\Feature;
use App\Models\{PublicationMode,PublicationType,User};
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class CandidatePublicationTest extends TestCase {
 use RefreshDatabase;protected function setUp():void{parent::setUp();$this->seed(DatabaseSeeder::class);}
 public function test_publication_masters_are_admin_editable_and_candidate_can_add_entry():void{$admin=User::where('email','admin@example.com')->firstOrFail();$this->actingAs($admin)->get(route('admin.shared-masters.index',['type'=>'publication-types']))->assertOk()->assertSee('Research Paper');$talent=User::where('email','talent@example.com')->firstOrFail();$type=PublicationType::where('code','RESEARCH_PAPER')->firstOrFail();$mode=PublicationMode::where('code','ONLINE')->firstOrFail();$this->actingAs($talent)->post(route('talent.profile.publication'),['publication_type_id'=>$type->id,'publication_mode_id'=>$mode->id,'area_of_publication'=>'Artificial Intelligence','publication_count'=>3,'title'=>'Responsible AI in Recruitment','publisher_name'=>'Academic Journal','publication_url'=>'https://example.test/paper','is_peer_reviewed'=>1])->assertRedirect();$this->assertDatabaseHas('candidate_publications',['candidate_profile_id'=>$talent->candidateProfile->id,'publication_count'=>3,'is_peer_reviewed'=>true]);$this->get(route('talent.profile.edit','publications'))->assertOk()->assertSee('Responsible AI in Recruitment')->assertSee('3 publications');}
}
