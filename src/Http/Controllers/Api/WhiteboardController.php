<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers\Api;

use Hekal\ClassBridge\ClassBridgeManager;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\Whiteboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class WhiteboardController extends Controller
{
    public function __construct(
        private readonly ClassBridgeManager $classBridge,
    ) {}

    public function store(string $session, Request $request): JsonResponse
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $board = $this->classBridge->whiteboard()->createBoard(
            $sessionModel,
            $validated['title'] ?? null,
        );

        return response()->json(['data' => $this->formatBoard($board)], 201);
    }

    public function addPage(string $session, string $whiteboard, Request $request): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $board = Whiteboard::query()->findOrFail($whiteboard);

        $validated = $request->validate([
            'content' => ['nullable', 'array'],
        ]);

        $page = $this->classBridge->whiteboard()->addPage($board, $validated['content'] ?? null);

        return response()->json([
            'data' => [
                'id' => $page->id,
                'page_number' => $page->page_number,
            ],
        ], 201);
    }

    public function snapshot(string $session, string $whiteboard): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $board = Whiteboard::query()->findOrFail($whiteboard);

        return response()->json([
            'data' => $this->classBridge->whiteboard()->snapshotMeta($board),
        ]);
    }

    public function lock(string $session, string $whiteboard): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $board = Whiteboard::query()->findOrFail($whiteboard);

        $updated = $this->classBridge->whiteboard()->lockWriting($board);

        return response()->json(['data' => $this->formatBoard($updated)]);
    }

    public function unlock(string $session, string $whiteboard): JsonResponse
    {
        ClassroomSession::query()->findOrFail($session);
        $board = Whiteboard::query()->findOrFail($whiteboard);

        $updated = $this->classBridge->whiteboard()->unlockWriting($board);

        return response()->json(['data' => $this->formatBoard($updated)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatBoard(Whiteboard $board): array
    {
        return [
            'id' => $board->id,
            'title' => $board->title,
            'status' => $board->status,
            'pages' => $board->pages?->map(fn ($page) => [
                'id' => $page->id,
                'page_number' => $page->page_number,
            ])->values()->all() ?? [],
        ];
    }
}
