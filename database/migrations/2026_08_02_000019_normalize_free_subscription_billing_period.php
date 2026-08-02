<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('subscription_plans')->where('price', 0)->update(['billing_period' => 'na']);
        DB::table('user_subscriptions')->where('price', 0)->update(['billing_period' => 'na']);
    }

    public function down(): void
    {
        DB::table('subscription_plans')->where('billing_period', 'na')->update(['billing_period' => 'monthly']);
        DB::table('user_subscriptions')->where('billing_period', 'na')->update(['billing_period' => 'monthly']);
    }
};
