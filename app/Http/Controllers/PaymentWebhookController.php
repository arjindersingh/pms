<?php
namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, string $provider): JsonResponse
    {
        $gateway=PaymentGateway::where('provider',$provider)->where('is_enabled',true)->firstOrFail();
        abort_unless(filled($gateway->webhook_secret), 503, 'Webhook signing is not configured.');
        $signature=(string)$request->header('X-Payment-Signature');
        abort_unless($signature !== '' && hash_equals(hash_hmac('sha256',$request->getContent(),$gateway->webhook_secret),$signature), 401, 'Invalid webhook signature.');
        return response()->json(['received'=>true],202);
    }
}
