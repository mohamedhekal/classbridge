<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers\Api;

use Hekal\ClassBridge\ClassBridgeManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class JoinController extends Controller
{
    public function __construct(
        private readonly ClassBridgeManager $classBridge,
    ) {}

    public function validateToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $token = $this->classBridge->joinTokens()->validate($validated['token']);

        return response()->json([
            'data' => [
                'session_id' => $token->session_id,
                'participant_id' => $token->participant_id,
                'expires_at' => $token->expires_at->toIso8601String(),
            ],
        ]);
    }

    public function join(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $token = $this->classBridge->joinTokens()->validate($validated['token']);
        $this->classBridge->joinTokens()->consume($token);

        $participant = $token->participant;
        $session = $token->session;

        if ($participant !== null && $session !== null) {
            $this->classBridge->waitingRoom()->join($session, $participant);
        }

        return response()->json([
            'data' => [
                'session_id' => $token->session_id,
                'participant_id' => $token->participant_id,
                'join_url' => $session?->joinUrl($validated['token']),
            ],
        ]);
    }
}
