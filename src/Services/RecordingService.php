<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Contracts\ClassroomProvider;
use Hekal\ClassBridge\DTOs\RecordingOptions;
use Hekal\ClassBridge\Enums\RecordingStatus;
use Hekal\ClassBridge\Events\RecordingReady;
use Hekal\ClassBridge\Events\RecordingStarted;
use Hekal\ClassBridge\Exceptions\ClassBridgeException;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\Recording;
use Illuminate\Support\Facades\Event;

final class RecordingService
{
    public function __construct(
        private readonly ClassroomProvider $provider,
    ) {}

    public function start(ClassroomSession $session, ?RecordingOptions $options = null): Recording
    {
        $options ??= new RecordingOptions;

        $result = $this->provider->startRecording(
            (string) $session->provider_room_id,
            $options,
        );

        $recording = Recording::query()->create([
            'session_id' => $session->id,
            'provider_recording_id' => $result->recordingId,
            'status' => $result->status,
            'meta' => $result->meta,
        ]);

        Event::dispatch(new RecordingStarted($recording));

        return $recording;
    }

    public function stop(ClassroomSession $session, Recording $recording): Recording
    {
        $result = $this->provider->stopRecording(
            (string) $session->provider_room_id,
            (string) $recording->provider_recording_id,
        );

        $recording->status = $result->status;
        $recording->save();

        return $recording;
    }

    public function markReady(Recording $recording, ?string $url = null): Recording
    {
        $recording->status = RecordingStatus::Ready;
        $recording->url = $url ?? "https://classbridge.test/recordings/{$recording->provider_recording_id}";
        $recording->save();

        Event::dispatch(new RecordingReady($recording));

        return $recording;
    }

    public function publishRecording(Recording $recording, ?string $url = null): Recording
    {
        if (! in_array($recording->status, [RecordingStatus::Processing, RecordingStatus::Recording], true)) {
            throw ClassBridgeException::invalidRecordingState(
                'Recording must be processing before it can be published.',
            );
        }

        if ($recording->status === RecordingStatus::Recording) {
            $recording->status = RecordingStatus::Processing;
            $recording->save();
        }

        return $this->markReady($recording, $url);
    }
}
