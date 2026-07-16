<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers\Api;

use Hekal\ClassBridge\ClassBridgeManager;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class BreakoutController extends Controller
{
    public function __construct(
        private readonly ClassBridgeManager $classBridge,
    ) {}

    public function createRooms(string $session, Request $request): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);

        $validated = $request->validate([
            'names' => ['required', 'array', 'min:1'],
            'names.*' => ['required', 'string', 'max:255'],
        ]);

        $rooms = $this->classBridge->breakouts()->createRooms($sessionModel, $validated['names']);

        return response()->json([
            'data' => array_map(fn ($room) => [
                'id' => $room->id,
                'name' => $room->name,
                'status' => $room->status,
                'provider_room_id' => $room->provider_room_id,
            ], $rooms),
        ], 201);
    }

    public function assign(string $session, Request $request): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);

        $validated = $request->validate([
            'mode' => ['nullable', 'in:manual,random'],
            'assignments' => ['nullable', 'array'],
        ]);

        $this->classBridge->breakouts()->assignParticipants(
            $sessionModel,
            $validated['assignments'] ?? [],
            $validated['mode'] ?? 'manual',
        );

        return response()->json(['data' => ['assigned' => true]]);
    }

    public function open(string $session): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);
        $rooms = $this->classBridge->breakouts()->open($sessionModel);

        return response()->json([
            'data' => array_map(fn ($room) => [
                'id' => $room->id,
                'status' => $room->status,
            ], $rooms),
        ]);
    }

    public function closeAll(string $session): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);
        $count = $this->classBridge->breakouts()->closeAll($sessionModel);

        return response()->json(['data' => ['closed' => $count]]);
    }

    public function broadcast(string $session, Request $request): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);

        $validated = $request->validate([
            'participant_id' => ['required', 'uuid'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $participant = SessionParticipant::query()->findOrFail($validated['participant_id']);

        $message = $this->classBridge->breakouts()->broadcastMessage(
            $sessionModel,
            $participant,
            $validated['body'],
        );

        return response()->json([
            'data' => [
                'id' => $message->id,
                'body' => $message->body,
                'type' => $message->type,
            ],
        ], 201);
    }
}
