<?php

namespace Tests\Feature;

use App\Models\AdSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_page_shows_configurable_ad_placeholders_by_default(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Top banner')
            ->assertSee('Content break')
            ->assertSee('Bottom banner')
            ->assertDontSee('pagead2.googlesyndication.com');
    }

    public function test_configured_home_page_outputs_responsive_google_ad_units(): void
    {
        AdSetting::current()->update([
            'enabled' => true,
            'publisher_id' => 'ca-pub-1234567890123456',
            'homepage_top_slot' => '1234567890',
            'homepage_middle_slot' => '2345678901',
            'homepage_bottom_slot' => '3456789012',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1234567890123456', false)
            ->assertSee('data-ad-slot="1234567890"', false)
            ->assertSee('data-ad-slot="2345678901"', false)
            ->assertSee('data-ad-slot="3456789012"', false)
            ->assertSee('data-full-width-responsive="true"', false);
    }

    public function test_super_administrator_can_update_google_ads_settings(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($administrator)->get(route('admin.ads.edit'))
            ->assertOk()
            ->assertSee('Google Ads')
            ->assertSee('Publisher ID');

        $this->actingAs($administrator)->put(route('admin.ads.update'), [
            'enabled' => '1',
            'auto_ads_enabled' => '1',
            'publisher_id' => 'ca-pub-1234567890123456',
            'homepage_top_enabled' => '1',
            'homepage_top_slot' => '1234567890',
            'homepage_middle_slot' => '2345678901',
            'homepage_bottom_slot' => '3456789012',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ad_settings', [
            'enabled' => true,
            'auto_ads_enabled' => true,
            'homepage_top_enabled' => true,
            'homepage_middle_enabled' => false,
            'publisher_id' => 'ca-pub-1234567890123456',
            'updated_by' => $administrator->id,
        ]);
    }

    public function test_google_ads_settings_reject_invalid_ids(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($administrator)->from(route('admin.ads.edit'))->put(route('admin.ads.update'), [
            'publisher_id' => '<script>alert(1)</script>',
            'homepage_top_slot' => 'not-a-slot',
        ])->assertRedirect(route('admin.ads.edit'))->assertSessionHasErrors(['publisher_id', 'homepage_top_slot']);
    }

    public function test_non_administrators_cannot_open_google_ads_settings(): void
    {
        $talent = User::query()->where('email', 'talent@example.com')->firstOrFail();

        $this->actingAs($talent)->get(route('admin.ads.edit'))->assertForbidden();
    }
}
