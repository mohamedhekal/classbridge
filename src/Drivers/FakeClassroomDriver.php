<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Drivers;

use Hekal\ClassBridge\Contracts\ClassroomProvider;
use Hekal\ClassBridge\DTOs\JoinCredentials;
use Hekal\ClassBridge\DTOs\ParticipantPermissions;
use Hekal\ClassBridge\DTOs\ProviderRoom;
use Hekal\ClassBridge\DTOs\RecordingOptions;
use Hekal\ClassBridge\DTOs\RecordingResult;
use Hekal\ClassBridge\DTOs\SessionData;
use Hekal\ClassBridge\Enums\MediaType;
use Hekal\ClassBridge\Enums\RecordingStatus;
use Hekal\ClassBridge\Exceptions\ClassBridgeException;
use Illuminate\Support\Str;

final class FakeClassroomDriver implements ClassroomProvider
{
    /** @var array<string, array<string, mixed>> */
    private array $rooms = [];

    /** @var array<string, array<string, mixed>> */
    private array $participants = [];

    /** @var array<string, array<string, mixed>> */
    private array $recordings = [];

    /** @var array<string, JoinCredentials> */
    private array $tokens = [];

    private int $roomSequence = 0;

    private int $recordingSequence = 0;

    public function createRoom(SessionData $session): ProviderRoom
    {
        $this->roomSequence++;
        $roomId = sprintf('fake-room-%04d', $this->roomSequence);

        $this->rooms[$roomId] = [
            'room_id' => $roomId,
            'session_id' => $session->id,
            'provider' => 'fake',
            'meta' => $session->meta,
        ];

        return new ProviderRoom(
            roomId: $roomId,
            provider: 'fake',
            meta: ['session_id' => $session->id],
        );
    }

    public function deleteRoom(string $roomId): void
    {
        if (! isset($this->rooms[$roomId])) {
            throw ClassBridgeException::roomNotFound($roomId);
        }

        unset($this->rooms[$roomId]);
    }

    public function createJoinToken(
        string $roomId,
        string $participantId,
        ParticipantPermissions $permissions,
        ?\DateTimeInterface $expiresAt = null,
    ): JoinCredentials {
        $this->ensureRoom($roomId);

        $token = Str::random(64);
        $credentials = new JoinCredentials(
            token: $token,
            roomId: $roomId,
            participantId: $participantId,
            url: "https://classbridge.test/join/{$token}",
            meta: $permissions->toArray(),
        );

        $this->tokens[$token] = $credentials;
        $this->participants["{$roomId}:{$participantId}"] = [
            'room_id' => $roomId,
            'participant_id' => $participantId,
            'permissions' => $permissions->toArray(),
            'muted' => [],
        ];

        return $credentials;
    }

    public function updatePermissions(
        string $roomId,
        string $participantId,
        ParticipantPermissions $permissions,
    ): void {
        $key = "{$roomId}:{$participantId}";

        if (! isset($this->participants[$key])) {
            $this->participants[$key] = [
                'room_id' => $roomId,
                'participant_id' => $participantId,
                'permissions' => [],
                'muted' => [],
            ];
        }

        $this->participants[$key]['permissions'] = $permissions->toArray();
    }

    public function muteParticipant(string $roomId, string $participantId, MediaType $media): void
    {
        $key = "{$roomId}:{$participantId}";

        if (! isset($this->participants[$key])) {
            $this->participants[$key] = [
                'room_id' => $roomId,
                'participant_id' => $participantId,
                'permissions' => [],
                'muted' => [],
            ];
        }

        $this->participants[$key]['muted'][$media->value] = true;
    }

    public function removeParticipant(string $roomId, string $participantId): void
    {
        unset($this->participants["{$roomId}:{$participantId}"]);
    }

    public function startRecording(string $roomId, RecordingOptions $options): RecordingResult
    {
        $this->ensureRoom($roomId);
        $this->recordingSequence++;
        $recordingId = sprintf('fake-rec-%04d', $this->recordingSequence);

        $this->recordings[$recordingId] = [
            'room_id' => $roomId,
            'status' => RecordingStatus::Recording,
            'options' => $options->meta,
        ];

        return new RecordingResult(
            recordingId: $recordingId,
            status: RecordingStatus::Recording,
        );
    }

    public function stopRecording(string $roomId, string $recordingId): RecordingResult
    {
        if (! isset($this->recordings[$recordingId])) {
            throw ClassBridgeException::roomNotFound($recordingId);
        }

        $this->recordings[$recordingId]['status'] = RecordingStatus::Processing;

        return new RecordingResult(
            recordingId: $recordingId,
            status: RecordingStatus::Processing,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getRoom(string $roomId): array
    {
        return $this->ensureRoom($roomId);
    }

    public function getToken(string $token): ?JoinCredentials
    {
        return $this->tokens[$token] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    private function ensureRoom(string $roomId): array
    {
        if (! isset($this->rooms[$roomId])) {
            throw ClassBridgeException::roomNotFound($roomId);
        }

        return $this->rooms[$roomId];
    }
}
