<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentGateway extends Model
{
    protected $fillable=['provider','name','is_enabled','test_mode','currencies','credentials','webhook_secret','webhook_url','percentage_fee','fixed_fee','minimum_amount','maximum_amount','position','instructions','updated_by'];
    protected $hidden=['credentials','webhook_secret'];
    protected function casts(): array { return ['is_enabled'=>'boolean','test_mode'=>'boolean','currencies'=>'array','credentials'=>'encrypted:array','webhook_secret'=>'encrypted','percentage_fee'=>'decimal:3','fixed_fee'=>'decimal:2','minimum_amount'=>'decimal:2','maximum_amount'=>'decimal:2']; }
    public function methods(): HasMany { return $this->hasMany(PaymentGatewayMethod::class)->orderBy('position'); }
}
