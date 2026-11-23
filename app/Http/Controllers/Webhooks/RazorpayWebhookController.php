<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request, RazorpayService $razorpayService): JsonResponse
    {
        $rawBody = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $payload = $request->json()->all();

        $providerEventId = $payload['event_id']
            ?? $request->header('X-Razorpay-Event-Id')
            ?? ('evt_' . md5($rawBody));

        $eventType = $payload['event'] ?? 'unknown';

        $result = $razorpayService->handleWebhook(
            providerEventId: $providerEventId,
            eventType: $eventType,
            payload: $payload,
            rawBody: $rawBody,
            signature: $signature
        );

        return response()->json($result, $result['code'] ?? 200);
    }
}
