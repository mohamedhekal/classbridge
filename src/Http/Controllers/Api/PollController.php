<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers\Api;

use Hekal\ClassBridge\ClassBridgeManager;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\Poll;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class PollController extends Controller
{
    public function __construct(
        private readonly ClassBridgeManager $classBridge,
    ) {}

    public function store(string $session, Request $request): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string', 'max:255'],
        ]);

        $poll = $this->classBridge->polls()->createPoll(
            $sessionModel,
            $validated['question'],
            $validated['options'],
        );

        return response()->json(['data' => $this->formatPoll($poll)], 201);
    }

    public function start(string $session, string $poll): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $pollModel = Poll::query()->with('options')->findOrFail($poll);

        $updated = $this->classBridge->polls()->start($pollModel);

        return response()->json(['data' => $this->formatPoll($updated)]);
    }

    public function close(string $session, string $poll): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $pollModel = Poll::query()->with('options')->findOrFail($poll);

        $updated = $this->classBridge->polls()->close($pollModel);

        return response()->json(['data' => $this->formatPoll($updated)]);
    }

    public function respond(string $session, string $poll, Request $request): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $pollModel = Poll::query()->findOrFail($poll);

        $validated = $request->validate([
            'participant_id' => ['required', 'uuid'],
            'poll_option_id' => ['required', 'integer'],
        ]);

        $participant = SessionParticipant::query()->findOrFail($validated['participant_id']);

        $response = $this->classBridge->polls()->respond(
            $pollModel,
            $participant,
            (int) $validated['poll_option_id'],
        );

        return response()->json([
            'data' => [
                'id' => $response->id,
                'poll_option_id' => $response->poll_option_id,
            ],
        ], 201);
    }

    public function results(string $session, string $poll): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $pollModel = Poll::query()->findOrFail($poll);

        return response()->json([
            'data' => $this->classBridge->polls()->results($pollModel),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPoll(Poll $poll): array
    {
        return [
            'id' => $poll->id,
            'question' => $poll->question,
            'status' => $poll->status,
            'started_at' => $poll->started_at?->toIso8601String(),
            'ended_at' => $poll->ended_at?->toIso8601String(),
            'options' => $poll->options->map(fn ($option) => [
                'id' => $option->id,
                'label' => $option->label,
            ])->values()->all(),
        ];
    }
}
