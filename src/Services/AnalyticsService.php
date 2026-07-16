<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Models\AttendanceEvent;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\HandRaise;
use Hekal\ClassBridge\Models\Message;
use Hekal\ClassBridge\Models\Poll;
use Hekal\ClassBridge\Models\PollResponse;
use Hekal\ClassBridge\Models\SessionParticipant;

final class AnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function sessionSummary(ClassroomSession $session): array
    {
        $attendanceCount = AttendanceEvent::query()
            ->where('session_id', $session->id)
            ->where('event', 'joined')
            ->distinct('participant_id')
            ->count('participant_id');

        $participantCount = SessionParticipant::query()
            ->where('session_id', $session->id)
            ->whereIn('role', ['student', 'guest'])
            ->count();

        $messageCount = Message::query()
            ->where('session_id', $session->id)
            ->count();

        $handRaiseCount = HandRaise::query()
            ->where('session_id', $session->id)
            ->count();

        $pollIds = Poll::query()
            ->where('session_id', $session->id)
            ->pluck('id');

        $pollResponseCount = PollResponse::query()
            ->whereIn('poll_id', $pollIds)
            ->count();

        $durationMinutes = null;

        if ($session->started_at !== null) {
            $end = $session->ended_at ?? now();
            $durationMinutes = $session->started_at->diffInMinutes($end);
        }

        return [
            'session_id' => $session->id,
            'title' => $session->title,
            'status' => $session->status->value,
            'attendance_count' => $attendanceCount,
            'participant_count' => $participantCount,
            'message_count' => $messageCount,
            'hand_raise_count' => $handRaiseCount,
            'poll_response_count' => $pollResponseCount,
            'duration_minutes' => $durationMinutes,
            'started_at' => $session->started_at?->toIso8601String(),
            'ended_at' => $session->ended_at?->toIso8601String(),
        ];
    }
}
