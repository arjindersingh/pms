<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('category', 32)->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->char('currency', 3)->default('USD');
            $table->string('billing_period', 16)->default('monthly');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['category', 'slug']);
        });

        Schema::create('portal_menu_subscription_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portal_menu_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
            $table->unique(['subscription_plan_id', 'portal_menu_id']);
        });

        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->decimal('price', 12, 2);
            $table->char('currency', 3);
            $table->string('billing_period', 16);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status', 'starts_at', 'ends_at']);
        });

        $now = now();
        foreach (['recruiter', 'talent'] as $category) {
            foreach ([
                ['Free', 'free', 0, 10],
                ['Intermediate', 'intermediate', 19, 20],
                ['Full', 'full', 49, 30],
            ] as [$name, $slug, $price, $position]) {
                DB::table('subscription_plans')->insert([
                    'category' => $category, 'name' => $name, 'slug' => $slug,
                    'description' => $name.' '.$category.' access plan.', 'price' => $price,
                    'currency' => 'USD', 'billing_period' => 'monthly', 'position' => $position,
                    'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        $menusByCategory = DB::table('portal_menus')
            ->join('portal_modules', 'portal_modules.id', '=', 'portal_menus.portal_module_id')
            ->whereIn('portal_modules.slug', ['recruitment', 'career'])
            ->select('portal_menus.id', 'portal_menus.position', 'portal_menus.route_name', 'portal_modules.slug')
            ->get()->groupBy(fn ($menu) => $menu->slug === 'recruitment' ? 'recruiter' : 'talent');

        foreach (DB::table('subscription_plans')->get() as $plan) {
            $menus = $menusByCategory->get($plan->category, collect())->values();
            $limit = $plan->slug === 'free' ? 2 : ($plan->slug === 'intermediate' ? max(2, (int) ceil($menus->count() * .65)) : $menus->count());
            foreach ($menus as $index => $menu) {
                $enabled = $index < $limit || ($plan->slug === 'free' && $menu->route_name !== null);
                DB::table('portal_menu_subscription_plan')->insert([
                    'subscription_plan_id' => $plan->id, 'portal_menu_id' => $menu->id,
                    'can_view' => $enabled, 'can_create' => $enabled && $plan->slug !== 'free',
                    'can_update' => $enabled && $plan->slug !== 'free', 'can_delete' => $enabled && $plan->slug === 'full',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        $freePlans = DB::table('subscription_plans')->where('slug', 'free')->pluck('id', 'category');
        DB::table('users')->join('user_types', 'user_types.id', '=', 'users.user_type_id')
            ->whereIn('user_types.category', ['recruiter', 'talent'])
            ->select('users.id', 'user_types.category')->orderBy('users.id')->each(function ($user) use ($freePlans, $now) {
                DB::table('user_subscriptions')->insert([
                    'user_id' => $user->id, 'subscription_plan_id' => $freePlans[$user->category],
                    'status' => 'active', 'starts_at' => $now, 'price' => 0, 'currency' => 'USD',
                    'billing_period' => 'monthly', 'created_at' => $now, 'updated_at' => $now,
                ]);
            });

        $adminModule = DB::table('portal_modules')->where('slug', 'administration')->first();
        if ($adminModule) {
            $parentId = DB::table('portal_menus')->where('slug', 'monetization')->value('id');
            $menuId = DB::table('portal_menus')->insertGetId([
                'portal_module_id' => $adminModule->id, 'parent_id' => $parentId, 'name' => 'Subscription Plans',
                'slug' => 'subscription-plans', 'route_name' => 'admin.subscription-plans.index', 'icon' => 'bi-credit-card',
                'position' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
            foreach (DB::table('user_roles')->where('category', 'administrator')->where('is_active', true)->get() as $role) {
                DB::table('portal_menu_user_role')->insert([
                    'user_role_id' => $role->id, 'portal_menu_id' => $menuId, 'can_view' => true,
                    'can_create' => ! str_contains($role->slug, 'auditor'), 'can_update' => ! str_contains($role->slug, 'auditor'),
                    'can_delete' => false, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('portal_menu_subscription_plan');
        Schema::dropIfExists('subscription_plans');
    }
};
