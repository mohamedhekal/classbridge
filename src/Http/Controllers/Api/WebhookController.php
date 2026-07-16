<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers\Api;

use Hekal\ClassBridge\Models\ProviderEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class WebhookController extends Controller
{
    public function handle(string $provider, Request $request): JsonResponse
    {
        $secret = (string) config('classbridge.webhooks.signing_secret');

        if ($secret !== '') {
            $signature = (string) $request->header('X-ClassBridge-Signature', '');
            $expected = hash_hmac('sha256', $request->getContent(), $secret);

            if (! hash_equals($expected, $signature)) {
                return response()->json(['message' => 'Invalid signature.'], 401);
            }
        }

        $payload = $request->all();

        $event = ProviderEvent::query()->create([
            'session_id' => $payload['session_id'] ?? null,
            'provider' => $provider,
            'event_type' => (string) ($payload['event_type'] ?? $payload['type'] ?? 'unknown'),
            'payload' => $payload,
            'processed_at' => now(),
        ]);

        return response()->json(['data' => ['id' => $event->id]], 202);
    }
}
