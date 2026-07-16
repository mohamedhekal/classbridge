<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Enums\AttendanceStatus;
use Hekal\ClassBridge\Events\AttendanceFinalized;
use Hekal\ClassBridge\Models\AttendanceEvent;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Support\Facades\Event;

final class AttendanceService
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function record(
        ClassroomSession $session,
        SessionParticipant $participant,
        string $event,
        ?\DateTimeInterface $occurredAt = null,
        array $meta = [],
    ): AttendanceEvent {
        return AttendanceEvent::query()->create([
            'session_id' => $session->id,
            'participant_id' => $participant->id,
            'event' => $event,
            'occurred_at' => $occurredAt ?? now(),
            'meta' => $meta,
        ]);
    }

    /**
     * @return array<string, AttendanceStatus>
     */
    public function finalize(ClassroomSession $session): array
    {
        $rules = (array) ($session->settings?->settings['attendance'] ?? config('classbridge.attendance'));
        $minimumMinutes = (int) ($rules['minimum_duration_minutes'] ?? 10);
        $lateThreshold = (int) ($rules['late_threshold_minutes'] ?? 5);

        $results = [];

        $participants = SessionParticipant::query()
            ->where('session_id', $session->id)
            ->whereIn('role', ['student', 'guest'])
            ->get();

        foreach ($participants as $participant) {
            $events = AttendanceEvent::query()
                ->where('participant_id', $participant->id)
                ->orderBy('occurred_at')
                ->get();

            $joined = $events->firstWhere('event', 'joined');
            $left = $events->firstWhere('event', 'left');

            if ($joined === null) {
                $results[$participant->id] = AttendanceStatus::Absent;

                continue;
            }

            $joinedAt = $joined->occurred_at;
            $leftAt = $left !== null ? $left->occurred_at : ($session->ended_at ?? now());
            $durationMinutes = $joinedAt->diffInMinutes($leftAt);

            $isLate = $session->starts_at !== null
                && $joinedAt->greaterThan($session->starts_at->copy()->addMinutes($lateThreshold));

            if ($durationMinutes >= $minimumMinutes && ! $isLate) {
                $results[$participant->id] = AttendanceStatus::Present;
            } elseif ($durationMinutes >= $minimumMinutes && $isLate) {
                $results[$participant->id] = AttendanceStatus::Late;
            } elseif ($durationMinutes > 0) {
                $results[$participant->id] = AttendanceStatus::Partial;
            } else {
                $results[$participant->id] = AttendanceStatus::Absent;
            }
        }

        $stringResults = array_map(fn (AttendanceStatus $s) => $s->value, $results);

        Event::dispatch(new AttendanceFinalized($session, $stringResults));

        return $results;
    }
}
