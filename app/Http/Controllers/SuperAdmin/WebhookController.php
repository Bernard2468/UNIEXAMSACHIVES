<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Paystack webhooks
     */
    public function paystack(Request $request)
    {
        // Get raw body for signature verification
        $payload = $request->getContent();
        $signature = $request->header('X-Paystack-Signature');

        if (!$signature) {
            Log::warning('Paystack webhook received without signature');
            return response()->json(['error' => 'No signature'], 400);
        }

        // Verify signature
        $paystackService = new PaystackService();
        
        if (!$paystackService->validateWebhookSignature($payload, $signature)) {
            Log::warning('Paystack webhook signature validation failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Parse payload
        $data = json_decode($payload, true);

        if (!$data) {
            Log::warning('Paystack webhook payload could not be parsed');
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Handle webhook
        $result = $paystackService->handleWebhook($data);

        if ($result) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'failed'], 400);
    }

    /**
     * Test webhook endpoint (for development)
     */
    public function test(Request $request)
    {
        if (!app()->environment('local')) {
            abort(403, 'Test endpoint only available in local environment');
        }

        Log::info('Test webhook received', $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Test webhook received successfully',
            'data' => $request->all(),
        ]);
    }
}

