<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PaymentGatewayMethod extends Model
{
    protected $fillable=['payment_gateway_id','code','name','icon','is_enabled','position'];
    protected function casts(): array { return ['is_enabled'=>'boolean']; }
    public function gateway(): BelongsTo { return $this->belongsTo(PaymentGateway::class,'payment_gateway_id'); }
}
