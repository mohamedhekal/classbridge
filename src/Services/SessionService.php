<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Contracts\ClassroomProvider;
use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\DTOs\ParticipantPermissions;
use Hekal\ClassBridge\DTOs\SessionData;
use Hekal\ClassBridge\Enums\ParticipantRole;
use Hekal\ClassBridge\Enums\ParticipantStatus;
use Hekal\ClassBridge\Enums\SessionStatus;
use Hekal\ClassBridge\Events\SessionCreated;
use Hekal\ClassBridge\Events\SessionEnded;
use Hekal\ClassBridge\Events\SessionScheduled;
use Hekal\ClassBridge\Events\SessionStarted;
use Hekal\ClassBridge\Exceptions\ClassBridgeException;
use Hekal\ClassBridge\Models\AuditLog;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\SessionParticipant;
use Hekal\ClassBridge\Models\SessionSetting;
use Hekal\ClassBridge\Support\PermissionKeys;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class SessionService
{
    public function __construct(
        private readonly ClassroomProvider $provider,
        private readonly SessionStateMachine $stateMachine,
        private readonly FeatureManifest $featureManifest,
        private readonly JoinTokenService $joinTokenService,
        private readonly ?string $tenantId = null,
    ) {}

    public function create(CreateSessionData $data): ClassroomSession
    {
        return DB::transaction(function () use ($data): ClassroomSession {
            $tenantId = $data->tenantId ?? $this->tenantId;
            $providerName = $data->provider ?? (string) config('classbridge.default', 'fake');

            $session = ClassroomSession::query()->create([
                'tenant_id' => $tenantId,
                'title' => $data->title,
                'status' => $data->initialStatus,
                'hostable_type' => $data->hostable['type'] ?? null,
                'hostable_id' => $data->hostable['id'] ?? null,
                'courseable_type' => $data->courseable['type'] ?? null,
                'courseable_id' => $data->courseable['id'] ?? null,
                'owner_type' => $data->owner['type'] ?? null,
                'owner_id' => $data->owner['id'] ?? null,
                'starts_at' => $data->startsAt,
                'ends_at' => $data->endsAt,
                'duration_minutes' => $data->durationMinutes,
                'provider' => $providerName,
            ]);

            $room = $this->provider->createRoom(new SessionData(
                id: $session->id,
                title: $session->title,
                status: $session->status,
                tenantId: $session->tenant_id,
                provider: $providerName,
            ));

            $session->provider_room_id = $room->roomId;
            $session->save();

            $teacherRole = $data->teacherRole ?? ParticipantRole::Teacher;
            $permissions = new ParticipantPermissions(
                canPublishAudio: true,
                canPublishVideo: true,
                canPublishScreen: true,
            );

            $teacher = SessionParticipant::query()->create([
                'session_id' => $session->id,
                'tenant_id' => $tenantId,
                'user_type' => $data->teacherUserType,
                'user_id' => $data->teacherUserId,
                'role' => $teacherRole,
                'display_name' => $data->teacherDisplayName ?? 'Teacher',
                'status' => ParticipantStatus::Invited,
                'can_publish_audio' => $permissions->canPublishAudio,
                'can_publish_video' => $permissions->canPublishVideo,
                'can_publish_screen' => $permissions->canPublishScreen,
            ]);

            $teacher->update(['provider_participant_id' => $teacher->id]);

            foreach (PermissionKeys::defaultsForRole($teacherRole->value) as $key) {
                $teacher->permissions()->create([
                    'key' => $key,
                    'granted' => true,
                ]);
            }

            $features = $this->featureManifest->merge($tenantId, null, $data->features);

            SessionSetting::query()->create([
                'session_id' => $session->id,
                'features' => $features,
                'settings' => [
                    'moderation' => config('classbridge.moderation'),
                    'attendance' => config('classbridge.attendance'),
                ],
            ]);

            $joinToken = $this->joinTokenService->issue($teacher);
            $teacher->setRelation('latestJoinToken', $joinToken);

            AuditLog::query()->create([
                'tenant_id' => $tenantId,
                'session_id' => $session->id,
                'action' => 'session.created',
                'meta' => ['title' => $session->title],
            ]);

            Event::dispatch(new SessionCreated($session->fresh(['settings', 'participants'])));

            return $session->fresh(['settings', 'participants']);
        });
    }

    public function schedule(ClassroomSession $session, ?\DateTimeInterface $startsAt = null, ?\DateTimeInterface $endsAt = null): ClassroomSession
    {
        $this->stateMachine->transition($session, SessionStatus::Scheduled);

        if ($startsAt !== null) {
            $session->starts_at = Carbon::parse($startsAt);
        }

        if ($endsAt !== null) {
            $session->ends_at = Carbon::parse($endsAt);
        }

        $session->save();

        Event::dispatch(new SessionScheduled($session));

        return $session;
    }

    public function openWaiting(ClassroomSession $session): ClassroomSession
    {
        $this->stateMachine->transition($session, SessionStatus::Waiting);
        $session->save();

        return $session;
    }

    public function markReady(ClassroomSession $session): ClassroomSession
    {
        $this->stateMachine->transition($session, SessionStatus::Ready);
        $session->save();

        return $session;
    }

    public function start(ClassroomSession $session): ClassroomSession
    {
        $this->stateMachine->transition($session, SessionStatus::Live);
        $session->started_at = Carbon::now();
        $session->save();

        Event::dispatch(new SessionStarted($session));

        return $session;
    }

    public function pause(ClassroomSession $session): ClassroomSession
    {
        $this->stateMachine->transition($session, SessionStatus::Paused);
        $session->save();

        return $session;
    }

    public function resume(ClassroomSession $session): ClassroomSession
    {
        $this->stateMachine->transition($session, SessionStatus::Live);
        $session->save();

        return $session;
    }

    public function end(ClassroomSession $session): ClassroomSession
    {
        $this->stateMachine->transition($session, SessionStatus::Ended);
        $session->ended_at = Carbon::now();
        $session->save();

        Event::dispatch(new SessionEnded($session));

        return $session;
    }

    public function cancel(ClassroomSession $session): ClassroomSession
    {
        $this->stateMachine->transition($session, SessionStatus::Cancelled);
        $session->save();

        if ($session->provider_room_id !== null) {
            $this->provider->deleteRoom($session->provider_room_id);
        }

        return $session;
    }

    public function find(string $id): ClassroomSession
    {
        $query = ClassroomSession::query()->where('id', $id);

        if ($this->tenantId !== null && config('classbridge.multi_tenant.enabled', true)) {
            $query->where('tenant_id', $this->tenantId);
        }

        $session = $query->first();

        if ($session === null) {
            throw ClassBridgeException::sessionNotFound($id);
        }

        return $session;
    }
}
