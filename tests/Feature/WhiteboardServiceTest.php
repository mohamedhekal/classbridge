<?php

declare(strict_types=1);

use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Facades\ClassBridge;

it('creates whiteboards with pages and write mode controls', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Whiteboard test'));

    $board = ClassBridge::whiteboard()->createBoard($session, 'Lesson board');

    expect($board->title)->toBe('Lesson board')
        ->and($board->pages)->toHaveCount(1)
        ->and($board->status)->toBe('active');

    $page = ClassBridge::whiteboard()->addPage($board, ['strokes' => [['x' => 1]]]);
    expect($page->page_number)->toBe(2);

    $snapshot = ClassBridge::whiteboard()->snapshotMeta($board->fresh());
    expect($snapshot['page_count'])->toBe(2)
        ->and($snapshot['pages'][1]['stroke_count'])->toBe(1);

    $locked = ClassBridge::whiteboard()->lockWriting($board->fresh());
    expect($locked->status)->toBe('teacher_only')
        ->and($locked->pages->first()->content['write_mode'])->toBe('teacher_only');

    $unlocked = ClassBridge::whiteboard()->unlockWriting($locked);
    expect($unlocked->status)->toBe('active')
        ->and($unlocked->pages->first()->content['write_mode'])->toBe('everyone');
});
