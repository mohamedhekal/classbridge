<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Contracts;

use Hekal\ClassBridge\DTOs\JoinCredentials;
use Hekal\ClassBridge\DTOs\ParticipantPermissions;
use Hekal\ClassBridge\DTOs\ProviderRoom;
use Hekal\ClassBridge\DTOs\RecordingOptions;
use Hekal\ClassBridge\DTOs\RecordingResult;
use Hekal\ClassBridge\DTOs\SessionData;
use Hekal\ClassBridge\Enums\MediaType;

interface ClassroomProvider
{
    public function createRoom(SessionData $session): ProviderRoom;

    public function deleteRoom(string $roomId): void;

    public function createJoinToken(
        string $roomId,
        string $participantId,
        ParticipantPermissions $permissions,
        ?\DateTimeInterface $expiresAt = null,
    ): JoinCredentials;

    public function updatePermissions(
        string $roomId,
        string $participantId,
        ParticipantPermissions $permissions,
    ): void;

    public function muteParticipant(string $roomId, string $participantId, MediaType $media): void;

    public function removeParticipant(string $roomId, string $participantId): void;

    public function startRecording(string $roomId, RecordingOptions $options): RecordingResult;

    public function stopRecording(string $roomId, string $recordingId): RecordingResult;
}
