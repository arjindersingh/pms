<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PaymentTransaction extends Model
{
    protected $fillable=['reference','user_id','user_subscription_id','subscription_plan_id','payment_gateway_id','payment_method','provider_transaction_id','status','subtotal','fee','tax','total','refunded_amount','currency','paid_at','failed_at','refunded_at','failure_reason','provider_payload','metadata'];
    protected $hidden=['provider_payload'];
    protected function casts(): array { return ['subtotal'=>'decimal:2','fee'=>'decimal:2','tax'=>'decimal:2','total'=>'decimal:2','refunded_amount'=>'decimal:2','paid_at'=>'datetime','failed_at'=>'datetime','refunded_at'=>'datetime','provider_payload'=>'encrypted:array','metadata'=>'array']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function gateway(): BelongsTo { return $this->belongsTo(PaymentGateway::class,'payment_gateway_id'); }
    public function plan(): BelongsTo { return $this->belongsTo(SubscriptionPlan::class,'subscription_plan_id'); }
}
