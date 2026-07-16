<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Contracts\ClassroomProvider;
use Hekal\ClassBridge\DTOs\ParticipantPermissions;
use Hekal\ClassBridge\Enums\MediaType;
use Hekal\ClassBridge\Enums\ParticipantRole;
use Hekal\ClassBridge\Enums\ParticipantStatus;
use Hekal\ClassBridge\Events\ParticipantLeft;
use Hekal\ClassBridge\Events\ParticipantMuted;
use Hekal\ClassBridge\Exceptions\UnauthorizedClassroomActionException;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\ModerationAction;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

final class ModerationService
{
    public function __construct(
        private readonly ClassroomProvider $provider,
    ) {}

    public function mute(
        ClassroomSession $session,
        SessionParticipant $actor,
        SessionParticipant $target,
        MediaType $media,
    ): SessionParticipant {
        $this->ensureCanModerate($actor, 'mute');

        if ($session->provider_room_id !== null && $target->provider_participant_id !== null) {
            $this->provider->muteParticipant(
                $session->provider_room_id,
                $target->provider_participant_id,
                $media,
            );
        }

        match ($media) {
            MediaType::Audio => $target->can_publish_audio = false,
            MediaType::Video => $target->can_publish_video = false,
            MediaType::Screen => $target->can_publish_screen = false,
        };

        $target->save();

        $this->logAction($session, $actor, $target, 'mute', ['media' => $media->value]);

        Event::dispatch(new ParticipantMuted($target, $media));

        return $target;
    }

    public function requestUnmute(
        ClassroomSession $session,
        SessionParticipant $target,
        MediaType $media,
    ): void {
        if (! config('classbridge.moderation.request_unmute', true)) {
            throw UnauthorizedClassroomActionException::forAction('request_unmute');
        }

        $this->logAction($session, null, $target, 'request_unmute', ['media' => $media->value]);
    }

    public function remove(
        ClassroomSession $session,
        SessionParticipant $actor,
        SessionParticipant $target,
    ): SessionParticipant {
        $this->ensureCanModerate($actor, 'remove');

        if ($session->provider_room_id !== null && $target->provider_participant_id !== null) {
            $this->provider->removeParticipant(
                $session->provider_room_id,
                $target->provider_participant_id,
            );
        }

        $target->status = ParticipantStatus::Removed;
        $target->left_at = Carbon::now();
        $target->save();

        $this->logAction($session, $actor, $target, 'remove');

        Event::dispatch(new ParticipantLeft($target));

        return $target;
    }

    public function ban(
        ClassroomSession $session,
        SessionParticipant $actor,
        SessionParticipant $target,
    ): SessionParticipant {
        $this->remove($session, $actor, $target);

        $target->status = ParticipantStatus::Banned;
        $target->save();

        $this->logAction($session, $actor, $target, 'ban');

        return $target;
    }

    public function lock(ClassroomSession $session, SessionParticipant $actor): ClassroomSession
    {
        $this->ensureCanModerate($actor, 'lock');

        $session->locked_at = Carbon::now();
        $session->save();

        $this->logAction($session, $actor, null, 'lock');

        return $session;
    }

    public function promote(
        ClassroomSession $session,
        SessionParticipant $actor,
        SessionParticipant $target,
        ParticipantRole $role,
    ): SessionParticipant {
        $this->ensureCanManage($actor, 'promote');

        $target->role = $role;
        $target->save();

        $this->logAction($session, $actor, $target, 'promote', ['role' => $role->value]);

        return $target;
    }

    public function updatePermissions(
        ClassroomSession $session,
        SessionParticipant $actor,
        SessionParticipant $target,
        ParticipantPermissions $permissions,
    ): SessionParticipant {
        $this->ensureCanModerate($actor, 'update_permissions');

        if ($session->provider_room_id !== null && $target->provider_participant_id !== null) {
            $this->provider->updatePermissions(
                $session->provider_room_id,
                $target->provider_participant_id,
                $permissions,
            );
        }

        $target->can_publish_audio = $permissions->canPublishAudio;
        $target->can_publish_video = $permissions->canPublishVideo;
        $target->can_publish_screen = $permissions->canPublishScreen;
        $target->save();

        $this->logAction($session, $actor, $target, 'update_permissions', $permissions->toArray());

        return $target;
    }

    private function ensureCanModerate(SessionParticipant $actor, string $action): void
    {
        if (! $actor->canModerate()) {
            throw UnauthorizedClassroomActionException::forAction($action);
        }
    }

    private function ensureCanManage(SessionParticipant $actor, string $action): void
    {
        if (! $actor->role->canManageSession()) {
            throw UnauthorizedClassroomActionException::forAction($action);
        }
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    private function logAction(
        ClassroomSession $session,
        ?SessionParticipant $actor,
        ?SessionParticipant $target,
        string $action,
        ?array $meta = null,
    ): void {
        ModerationAction::query()->create([
            'session_id' => $session->id,
            'actor_participant_id' => $actor?->id,
            'target_participant_id' => $target?->id,
            'action' => $action,
            'meta' => $meta,
        ]);
    }
}
