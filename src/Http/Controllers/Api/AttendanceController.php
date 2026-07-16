<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers\Api;

use Hekal\ClassBridge\ClassBridgeManager;
use Hekal\ClassBridge\Models\ClassroomSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class AttendanceController extends Controller
{
    public function __construct(
        private readonly ClassBridgeManager $classBridge,
    ) {}

    public function finalize(string $session): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->with('settings')->findOrFail($session);
        $results = $this->classBridge->attendance()->finalize($sessionModel);

        $formatted = [];
        foreach ($results as $participantId => $status) {
            $formatted[$participantId] = $status->value;
        }

        return response()->json(['data' => $formatted]);
    }
}
