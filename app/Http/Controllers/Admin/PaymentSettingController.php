<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.payments.edit', ['gateways'=>PaymentGateway::with('methods')->orderBy('position')->get()]);
    }

    public function update(Request $request, PaymentGateway $gateway): RedirectResponse
    {
        $data=$request->validate([
            'name'=>['required','string','max:100'], 'currencies'=>['required','string','max:250'],
            'percentage_fee'=>['required','numeric','min:0','max:100'], 'fixed_fee'=>['required','numeric','min:0'],
            'minimum_amount'=>['nullable','numeric','min:0'], 'maximum_amount'=>['nullable','numeric','gt:minimum_amount'],
            'position'=>['required','integer','min:0'], 'instructions'=>['nullable','string','max:3000'],
            'credentials'=>['array'], 'credentials.*'=>['nullable','string','max:1000'], 'webhook_secret'=>['nullable','string','max:1000'],
            'methods'=>['array'],
        ]);
        $currencies=collect(preg_split('/[\s,]+/', strtoupper($data['currencies'])))->filter()->unique()->values();
        if ($currencies->contains(fn($currency)=>!preg_match('/^[A-Z]{3}$/',$currency))) return back()->withErrors(['currencies'=>'Use three-letter currency codes separated by commas.'])->withInput();
        $credentials=array_filter($data['credentials'] ?? [], fn($value)=>filled($value));
        $gateway->update([
            'name'=>$data['name'],'is_enabled'=>$request->boolean('is_enabled'),'test_mode'=>$request->boolean('test_mode'),
            'currencies'=>$currencies->all(),'credentials'=>array_merge($gateway->credentials ?? [],$credentials),
            'webhook_secret'=>filled($data['webhook_secret'] ?? null)?$data['webhook_secret']:$gateway->webhook_secret,
            'webhook_url'=>route('payments.webhook',$gateway->provider),'percentage_fee'=>$data['percentage_fee'],'fixed_fee'=>$data['fixed_fee'],
            'minimum_amount'=>$data['minimum_amount'] ?? null,'maximum_amount'=>$data['maximum_amount'] ?? null,
            'position'=>$data['position'],'instructions'=>$data['instructions'] ?? null,'updated_by'=>$request->user()->id,
        ]);
        foreach ($gateway->methods as $method) $method->update(['is_enabled'=>$request->boolean("methods.{$method->id}")]);
        return back()->with('status', "{$gateway->name} settings saved securely.");
    }

    public function transactions(Request $request): View
    {
        $query=PaymentTransaction::query()->with(['user','gateway','plan'])->latest();
        $query->when($request->filled('status'),fn($q)=>$q->where('status',$request->string('status')));
        $query->when($request->filled('search'),fn($q)=>$q->where(fn($i)=>$i->where('reference','like','%'.$request->string('search').'%')->orWhere('provider_transaction_id','like','%'.$request->string('search').'%')));
        return view('admin.payments.transactions',['transactions'=>$query->paginate(25)->withQueryString()]);
    }
}
