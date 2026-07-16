<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers\Api;

use Hekal\ClassBridge\ClassBridgeManager;
use Hekal\ClassBridge\Models\ClassroomSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class AnalyticsController extends Controller
{
    public function __construct(
        private readonly ClassBridgeManager $classBridge,
    ) {}

    public function sessionSummary(string $session): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);

        return response()->json([
            'data' => $this->classBridge->analytics()->sessionSummary($sessionModel),
        ]);
    }
}
