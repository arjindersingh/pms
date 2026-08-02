<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id(); $table->string('provider')->unique(); $table->string('name');
            $table->boolean('is_enabled')->default(false); $table->boolean('test_mode')->default(true);
            $table->json('currencies')->nullable(); $table->text('credentials')->nullable();
            $table->text('webhook_secret')->nullable(); $table->string('webhook_url')->nullable();
            $table->decimal('percentage_fee', 6, 3)->default(0); $table->decimal('fixed_fee', 12, 2)->default(0);
            $table->decimal('minimum_amount', 12, 2)->nullable(); $table->decimal('maximum_amount', 12, 2)->nullable();
            $table->unsignedInteger('position')->default(0); $table->text('instructions')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });
        Schema::create('payment_gateway_methods', function (Blueprint $table) {
            $table->id(); $table->foreignId('payment_gateway_id')->constrained()->cascadeOnDelete();
            $table->string('code'); $table->string('name'); $table->string('icon')->nullable();
            $table->boolean('is_enabled')->default(true); $table->unsignedInteger('position')->default(0); $table->timestamps();
            $table->unique(['payment_gateway_id', 'code']);
        });
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id(); $table->uuid('reference')->unique(); $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_gateway_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_method')->nullable(); $table->string('provider_transaction_id')->nullable()->index();
            $table->string('status', 24)->default('pending')->index(); $table->decimal('subtotal', 12, 2);
            $table->decimal('fee', 12, 2)->default(0); $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2); $table->decimal('refunded_amount', 12, 2)->default(0); $table->char('currency', 3);
            $table->timestamp('paid_at')->nullable(); $table->timestamp('failed_at')->nullable(); $table->timestamp('refunded_at')->nullable();
            $table->text('failure_reason')->nullable(); $table->json('provider_payload')->nullable(); $table->json('metadata')->nullable(); $table->timestamps();
        });

        $now = now();
        $definitions = [
            ['stripe','Stripe',['card','apple_pay','google_pay']], ['paypal','PayPal',['paypal']],
            ['razorpay','Razorpay',['card','upi','netbanking','wallet']], ['bank_transfer','Bank Transfer',['bank_transfer']],
        ];
        foreach ($definitions as $position => [$provider,$name,$methods]) {
            $gatewayId = DB::table('payment_gateways')->insertGetId(['provider'=>$provider,'name'=>$name,'is_enabled'=>false,'test_mode'=>true,'currencies'=>json_encode(['USD']),'position'=>($position+1)*10,'created_at'=>$now,'updated_at'=>$now]);
            foreach ($methods as $methodPosition => $code) DB::table('payment_gateway_methods')->insert(['payment_gateway_id'=>$gatewayId,'code'=>$code,'name'=>str($code)->headline(),'is_enabled'=>true,'position'=>($methodPosition+1)*10,'created_at'=>$now,'updated_at'=>$now]);
        }

        $moduleId = DB::table('portal_modules')->where('slug','administration')->value('id');
        $parentId = DB::table('portal_menus')->where('slug','monetization')->value('id');
        if ($moduleId) {
            $menuId = DB::table('portal_menus')->insertGetId(['portal_module_id'=>$moduleId,'parent_id'=>$parentId,'name'=>'Payment Settings','slug'=>'payment-settings','route_name'=>'admin.payments.edit','icon'=>'bi-wallet2','position'=>30,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
            foreach (DB::table('user_roles')->where('category','administrator')->get() as $role) DB::table('portal_menu_user_role')->insert(['user_role_id'=>$role->id,'portal_menu_id'=>$menuId,'can_view'=>true,'can_create'=>false,'can_update'=>!str_contains($role->slug,'auditor'),'can_delete'=>false,'created_at'=>$now,'updated_at'=>$now]);
        }
    }

    public function down(): void
    {
        DB::table('portal_menus')->where('slug','payment-settings')->delete();
        Schema::dropIfExists('payment_transactions'); Schema::dropIfExists('payment_gateway_methods'); Schema::dropIfExists('payment_gateways');
    }
};
