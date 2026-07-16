<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\Whiteboard;
use Hekal\ClassBridge\Models\WhiteboardPage;

final class WhiteboardService
{
    public function createBoard(ClassroomSession $session, ?string $title = null): Whiteboard
    {
        $board = Whiteboard::query()->create([
            'session_id' => $session->id,
            'title' => $title ?? 'Whiteboard',
            'status' => 'active',
        ]);

        $this->addPage($board);

        return $board->load('pages');
    }

    public function addPage(Whiteboard $whiteboard, ?array $content = null): WhiteboardPage
    {
        $nextPage = ((int) $whiteboard->pages()->max('page_number')) + 1;

        return WhiteboardPage::query()->create([
            'whiteboard_id' => $whiteboard->id,
            'page_number' => max(1, $nextPage),
            'content' => $content ?? ['strokes' => [], 'write_mode' => 'everyone'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshotMeta(Whiteboard $whiteboard): array
    {
        $whiteboard->load('pages');

        return [
            'whiteboard_id' => $whiteboard->id,
            'title' => $whiteboard->title,
            'status' => $whiteboard->status,
            'page_count' => $whiteboard->pages->count(),
            'pages' => $whiteboard->pages->map(fn (WhiteboardPage $page): array => [
                'id' => $page->id,
                'page_number' => $page->page_number,
                'stroke_count' => count((array) (($page->content['strokes'] ?? []))),
            ])->values()->all(),
        ];
    }

    public function lockWriting(Whiteboard $whiteboard): Whiteboard
    {
        $whiteboard->status = 'teacher_only';
        $whiteboard->save();

        $whiteboard->load('pages');
        $this->syncWriteModeOnPages($whiteboard, 'teacher_only');

        return $whiteboard->fresh('pages');
    }

    public function unlockWriting(Whiteboard $whiteboard): Whiteboard
    {
        $whiteboard->status = 'active';
        $whiteboard->save();

        $whiteboard->load('pages');
        $this->syncWriteModeOnPages($whiteboard, 'everyone');

        return $whiteboard->fresh('pages');
    }

    private function syncWriteModeOnPages(Whiteboard $whiteboard, string $mode): void
    {
        foreach ($whiteboard->pages as $page) {
            $content = (array) ($page->content ?? []);
            $content['write_mode'] = $mode;
            $page->content = $content;
            $page->save();
        }
    }
}
