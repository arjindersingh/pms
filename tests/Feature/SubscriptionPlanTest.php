<?php

namespace Tests\Feature;

use App\Models\PortalMenu;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\PortalAccess;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_subscription_plan_controls_menu_access_and_individual_snapshot_is_ignored(): void
    {
        $user = User::where('email', 'recruiter@example.com')->firstOrFail();
        $menu = PortalMenu::where('slug', 'job-postings')->firstOrFail();
        $plan = SubscriptionPlan::where('category', 'recruiter')->where('slug', 'full')->firstOrFail();

        $user->subscriptions()->where('status', 'active')->update(['status' => 'replaced', 'ends_at' => now()]);
        $user->subscriptions()->create(['subscription_plan_id' => $plan->id, 'status' => 'active', 'starts_at' => now(), 'price' => $plan->price, 'currency' => $plan->currency, 'billing_period' => $plan->billing_period]);
        $user->permittedMenus()->syncWithoutDetaching([$menu->id => ['can_view' => false, 'can_create' => false, 'can_update' => false, 'can_delete' => false]]);

        $this->assertTrue(app(PortalAccess::class)->menu($user->fresh(['userType', 'role']), $menu, 'view'));
        $this->assertTrue(app(PortalAccess::class)->menu($user->fresh(['userType', 'role']), $menu, 'delete'));
    }

    public function test_admin_can_assign_a_paid_plan_and_price_is_snapshotted(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $talent = User::where('email', 'talent@example.com')->firstOrFail();
        $plan = SubscriptionPlan::where('category', 'talent')->where('slug', 'intermediate')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.accounts.subscription', $talent), ['subscription_plan_id' => $plan->id])->assertRedirect();

        $this->assertDatabaseHas('user_subscriptions', ['user_id' => $talent->id, 'subscription_plan_id' => $plan->id, 'status' => 'active', 'price' => $plan->price]);
        $this->assertSame(1, $talent->subscriptions()->where('status', 'active')->count());
    }

    public function test_new_registration_receives_free_subscription(): void
    {
        $this->post(route('register.talent.store'), ['name' => 'Subscriber', 'email' => 'subscriber@example.com', 'password' => 'Password123', 'password_confirmation' => 'Password123', 'terms' => true])->assertRedirect(route('talent.dashboard'));
        $user = User::where('email', 'subscriber@example.com')->firstOrFail();
        $this->assertSame('free', $user->activeSubscription()->firstOrFail()->plan->slug);
        $this->assertSame('na', $user->activeSubscription()->firstOrFail()->billing_period);
    }

    public function test_admin_can_create_daily_and_lifetime_plans(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        foreach (['daily', 'lifetime', 'one_time'] as $position => $period) {
            $this->actingAs($admin)->post(route('admin.subscription-plans.store'), [
                'category' => 'talent',
                'name' => ucfirst(str_replace('_', ' ', $period)).' plan',
                'price' => 10,
                'currency' => 'USD',
                'billing_period' => $period,
                'position' => 100 + $position,
                'is_active' => 1,
            ])->assertRedirect();

            $this->assertDatabaseHas('subscription_plans', ['category' => 'talent', 'billing_period' => $period]);
        }
    }

    public function test_free_plan_is_always_saved_with_na_billing_period(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.subscription-plans.store'), [
            'category' => 'recruiter',
            'name' => 'Another free plan',
            'price' => 0,
            'currency' => 'USD',
            'billing_period' => 'monthly',
            'position' => 100,
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('subscription_plans', [
            'category' => 'recruiter',
            'name' => 'Another free plan',
            'billing_period' => 'na',
        ]);
    }
}
