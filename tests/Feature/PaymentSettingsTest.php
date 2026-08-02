<?php
namespace Tests\Feature;

use App\Models\PaymentGateway;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSettingsTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->seed(DatabaseSeeder::class); }

    public function test_admin_can_save_gateway_credentials_without_exposing_plaintext(): void
    {
        $admin=User::where('email','admin@example.com')->firstOrFail(); $gateway=PaymentGateway::where('provider','stripe')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.payments.update',$gateway),[
            'name'=>'Stripe','is_enabled'=>1,'test_mode'=>1,'currencies'=>'USD, EUR','percentage_fee'=>'2.9','fixed_fee'=>'0.30','position'=>10,
            'credentials'=>['publishable_key'=>'pk_test_example','secret_key'=>'sk_test_secret'],'webhook_secret'=>'whsec_example',
            'methods'=>[$gateway->methods()->first()->id=>1],
        ])->assertRedirect();
        $fresh=$gateway->fresh(); $this->assertSame('sk_test_secret',$fresh->credentials['secret_key']);
        $this->assertStringNotContainsString('sk_test_secret',(string)\DB::table('payment_gateways')->where('id',$gateway->id)->value('credentials'));
        $this->actingAs($admin)->get(route('admin.payments.edit'))->assertOk()->assertDontSee('sk_test_secret');
    }

    public function test_webhook_requires_valid_signature(): void
    {
        $gateway=PaymentGateway::where('provider','stripe')->firstOrFail(); $gateway->update(['is_enabled'=>true,'webhook_secret'=>'secret']);
        $payload='{"event":"payment.paid"}';
        $this->call('POST',route('payments.webhook','stripe'),[],[],[],['HTTP_X_PAYMENT_SIGNATURE'=>hash_hmac('sha256',$payload,'secret'),'CONTENT_TYPE'=>'application/json'],$payload)->assertAccepted();
        $this->postJson(route('payments.webhook','stripe'),['event'=>'payment.paid'],['X-Payment-Signature'=>'wrong'])->assertUnauthorized();
    }
}
