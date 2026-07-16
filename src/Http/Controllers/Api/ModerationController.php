<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers\Api;

use Hekal\ClassBridge\ClassBridgeManager;
use Hekal\ClassBridge\Enums\MediaType;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ModerationController extends Controller
{
    public function __construct(
        private readonly ClassBridgeManager $classBridge,
    ) {}

    public function mute(string $session, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'actor_participant_id' => ['required', 'uuid'],
            'target_participant_id' => ['required', 'uuid'],
            'media' => ['required', 'string', 'in:audio,video,screen'],
        ]);

        $sessionModel = ClassroomSession::query()->findOrFail($session);
        $actor = SessionParticipant::query()->findOrFail($validated['actor_participant_id']);
        $target = SessionParticipant::query()->findOrFail($validated['target_participant_id']);

        $updated = $this->classBridge->moderation()->mute(
            $sessionModel,
            $actor,
            $target,
            MediaType::from($validated['media']),
        );

        return response()->json(['data' => ['participant_id' => $updated->id]]);
    }

    public function remove(string $session, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'actor_participant_id' => ['required', 'uuid'],
            'target_participant_id' => ['required', 'uuid'],
        ]);

        $sessionModel = ClassroomSession::query()->findOrFail($session);
        $actor = SessionParticipant::query()->findOrFail($validated['actor_participant_id']);
        $target = SessionParticipant::query()->findOrFail($validated['target_participant_id']);

        $updated = $this->classBridge->moderation()->remove($sessionModel, $actor, $target);

        return response()->json(['data' => ['participant_id' => $updated->id, 'status' => $updated->status->value]]);
    }
}
