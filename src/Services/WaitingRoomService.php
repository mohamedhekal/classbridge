<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Enums\ParticipantStatus;
use Hekal\ClassBridge\Events\ParticipantAdmitted;
use Hekal\ClassBridge\Events\ParticipantEnteredWaitingRoom;
use Hekal\ClassBridge\Events\ParticipantJoined;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\SessionParticipant;
use Hekal\ClassBridge\Models\WaitingEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

final class WaitingRoomService
{
    public function enter(ClassroomSession $session, SessionParticipant $participant): WaitingEntry
    {
        $participant->status = ParticipantStatus::Waiting;
        $participant->save();

        $entry = WaitingEntry::query()->create([
            'session_id' => $session->id,
            'participant_id' => $participant->id,
            'display_name' => $participant->display_name,
            'status' => 'pending',
        ]);

        Event::dispatch(new ParticipantEnteredWaitingRoom($entry, $participant));

        return $entry;
    }

    public function admit(ClassroomSession $session, WaitingEntry $entry): SessionParticipant
    {
        $entry->status = 'admitted';
        $entry->save();

        $participant = $entry->participant ?? SessionParticipant::query()->findOrFail($entry->participant_id);

        $participant->status = ParticipantStatus::Admitted;
        $participant->save();

        Event::dispatch(new ParticipantAdmitted($participant));

        return $participant;
    }

    public function admitAll(ClassroomSession $session): int
    {
        $entries = WaitingEntry::query()
            ->where('session_id', $session->id)
            ->where('status', 'pending')
            ->get();

        $count = 0;

        foreach ($entries as $entry) {
            $this->admit($session, $entry);
            $count++;
        }

        return $count;
    }

    public function reject(ClassroomSession $session, WaitingEntry $entry): WaitingEntry
    {
        $entry->status = 'rejected';
        $entry->save();

        if ($entry->participant_id !== null) {
            $participant = SessionParticipant::query()->find($entry->participant_id);
            if ($participant !== null) {
                $participant->status = ParticipantStatus::Removed;
                $participant->save();
            }
        }

        return $entry;
    }

    public function join(ClassroomSession $session, SessionParticipant $participant): SessionParticipant
    {
        $participant->status = ParticipantStatus::Joined;
        $participant->joined_at = Carbon::now();
        $participant->save();

        Event::dispatch(new ParticipantJoined($participant));

        return $participant;
    }
}
