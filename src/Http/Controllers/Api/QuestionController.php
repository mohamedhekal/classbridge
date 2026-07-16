<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers\Api;

use Hekal\ClassBridge\ClassBridgeManager;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\Question;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class QuestionController extends Controller
{
    public function __construct(
        private readonly ClassBridgeManager $classBridge,
    ) {}

    public function store(string $session, Request $request): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);

        $validated = $request->validate([
            'participant_id' => ['required', 'uuid'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $participant = SessionParticipant::query()->findOrFail($validated['participant_id']);

        $question = $this->classBridge->questions()->ask(
            $sessionModel,
            $participant,
            $validated['body'],
        );

        return response()->json(['data' => $this->formatQuestion($question)], 201);
    }

    public function upvote(string $session, int $question): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $questionModel = Question::query()->findOrFail($question);

        $updated = $this->classBridge->questions()->upvote($questionModel);

        return response()->json(['data' => $this->formatQuestion($updated)]);
    }

    public function markAnswered(string $session, int $question): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $questionModel = Question::query()->findOrFail($question);

        $updated = $this->classBridge->questions()->markAnswered($questionModel);

        return response()->json(['data' => $this->formatQuestion($updated)]);
    }

    public function pin(string $session, int $question): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $questionModel = Question::query()->findOrFail($question);

        $updated = $this->classBridge->questions()->pin($questionModel);

        return response()->json(['data' => $this->formatQuestion($updated)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatQuestion(Question $question): array
    {
        return [
            'id' => $question->id,
            'body' => $question->body,
            'status' => $question->status,
            'meta' => $question->meta,
        ];
    }
}
