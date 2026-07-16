<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers\Api;

use Hekal\ClassBridge\ClassBridgeManager;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\Recording;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class RecordingController extends Controller
{
    public function __construct(
        private readonly ClassBridgeManager $classBridge,
    ) {}

    public function start(string $session): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);
        $recording = $this->classBridge->recording()->start($sessionModel);

        return response()->json([
            'data' => [
                'id' => $recording->id,
                'provider_recording_id' => $recording->provider_recording_id,
                'status' => $recording->status->value,
            ],
        ], 201);
    }

    public function stop(string $session, int $recording): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);
        $recordingModel = Recording::query()->findOrFail($recording);
        $updated = $this->classBridge->recording()->stop($sessionModel, $recordingModel);

        return response()->json([
            'data' => [
                'id' => $updated->id,
                'status' => $updated->status->value,
            ],
        ]);
    }

    public function publish(string $session, int $recording): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $recordingModel = Recording::query()->findOrFail($recording);
        $updated = $this->classBridge->recording()->publishRecording($recordingModel);

        return response()->json([
            'data' => [
                'id' => $updated->id,
                'status' => $updated->status->value,
                'url' => $updated->url,
            ],
        ]);
    }
}
