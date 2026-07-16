<?php

declare(strict_types=1);

use Hekal\ClassBridge\ClassBridgeManager;
use Hekal\ClassBridge\Drivers\FakeClassroomDriver;
use Hekal\ClassBridge\DTOs\ParticipantPermissions;
use Hekal\ClassBridge\DTOs\RecordingOptions;
use Hekal\ClassBridge\DTOs\SessionData;
use Hekal\ClassBridge\Enums\MediaType;
use Hekal\ClassBridge\Enums\RecordingStatus;
use Hekal\ClassBridge\Enums\SessionStatus;
use Hekal\ClassBridge\Exceptions\ClassBridgeException;

it('implements the classroom provider contract with fake driver', function () {
    $driver = new FakeClassroomDriver;

    $room = $driver->createRoom(new SessionData(
        id: 'session-uuid',
        title: 'Contract test',
        status: SessionStatus::Draft,
    ));

    expect($room->roomId)->toStartWith('fake-room-')
        ->and($room->provider)->toBe('fake');

    $permissions = new ParticipantPermissions(
        canPublishAudio: true,
        canPublishVideo: true,
        canPublishScreen: false,
    );

    $credentials = $driver->createJoinToken(
        $room->roomId,
        'participant-1',
        $permissions,
    );

    expect($credentials->token)->not->toBeEmpty()
        ->and($driver->getToken($credentials->token))->not->toBeNull();

    $driver->updatePermissions($room->roomId, 'participant-1', new ParticipantPermissions(
        canPublishAudio: false,
        canPublishVideo: true,
        canPublishScreen: false,
    ));

    $driver->muteParticipant($room->roomId, 'participant-1', MediaType::Audio);

    $recording = $driver->startRecording($room->roomId, new RecordingOptions);
    expect($recording->status)->toBe(RecordingStatus::Recording);

    $stopped = $driver->stopRecording($room->roomId, $recording->recordingId);
    expect($stopped->status)->toBe(RecordingStatus::Processing);

    $driver->removeParticipant($room->roomId, 'participant-1');
    $driver->deleteRoom($room->roomId);

    $driver->deleteRoom($room->roomId);
})->throws(ClassBridgeException::class);

it('resolves fake driver from the manager', function () {
    $driver = app(ClassBridgeManager::class)->driver('fake');

    expect($driver)->toBeInstanceOf(FakeClassroomDriver::class);
});
