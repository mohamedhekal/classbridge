<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Contracts\ClassroomProvider;
use Hekal\ClassBridge\DTOs\SessionData;
use Hekal\ClassBridge\Models\BreakoutParticipant;
use Hekal\ClassBridge\Models\BreakoutRoom;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\Message;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class BreakoutService
{
    public function __construct(
        private readonly ClassroomProvider $provider,
    ) {}

    /**
     * @param  list<string>  $names
     * @return list<BreakoutRoom>
     */
    public function createRooms(ClassroomSession $session, array $names): array
    {
        return DB::transaction(function () use ($session, $names): array {
            $rooms = [];

            foreach ($names as $index => $name) {
                $room = $this->provider->createRoom(new SessionData(
                    id: "{$session->id}-breakout-{$index}",
                    title: $name,
                    status: $session->status,
                    tenantId: $session->tenant_id,
                    provider: $session->provider,
                ));

                $rooms[] = BreakoutRoom::query()->create([
                    'session_id' => $session->id,
                    'name' => $name,
                    'provider_room_id' => $room->roomId,
                    'status' => 'pending',
                ]);
            }

            return $rooms;
        });
    }

    /**
     * @param  array<string, list<string>>  $assignments  room_id => [participant_id, ...]
     */
    public function assignParticipants(ClassroomSession $session, array $assignments = [], string $mode = 'manual'): void
    {
        if ($mode === 'random') {
            $roomCount = count($assignments) > 0
                ? count($assignments)
                : max(1, BreakoutRoom::query()->where('session_id', $session->id)->count());

            $this->assignRandomly($session, $roomCount);

            return;
        }

        DB::transaction(function () use ($assignments): void {
            foreach ($assignments as $roomId => $participantIds) {
                foreach ($participantIds as $participantId) {
                    BreakoutParticipant::query()->updateOrCreate(
                        [
                            'breakout_room_id' => $roomId,
                            'participant_id' => $participantId,
                        ],
                        [
                            'joined_at' => now(),
                            'left_at' => null,
                        ],
                    );
                }
            }
        });
    }

    /**
     * @return list<BreakoutRoom>
     */
    public function open(ClassroomSession $session): array
    {
        $rooms = BreakoutRoom::query()
            ->where('session_id', $session->id)
            ->get();

        foreach ($rooms as $room) {
            $room->status = 'open';
            $room->save();
        }

        return $rooms->all();
    }

    public function closeAll(ClassroomSession $session): int
    {
        return BreakoutRoom::query()
            ->where('session_id', $session->id)
            ->update([
                'status' => 'closed',
            ]);
    }

    public function broadcastMessage(
        ClassroomSession $session,
        SessionParticipant $from,
        string $body,
    ): Message {
        return Message::query()->create([
            'session_id' => $session->id,
            'participant_id' => $from->id,
            'body' => $body,
            'type' => 'breakout_broadcast',
            'meta' => ['scope' => 'all_breakouts'],
        ]);
    }

    private function assignRandomly(ClassroomSession $session, int $roomCount): void
    {
        $rooms = BreakoutRoom::query()
            ->where('session_id', $session->id)
            ->get();

        if ($rooms->isEmpty()) {
            $rooms = collect($this->createRooms(
                $session,
                array_map(fn (int $i) => "Room {$i}", range(1, $roomCount)),
            ));
        }

        /** @var Collection<int, SessionParticipant> $participants */
        $participants = SessionParticipant::query()
            ->where('session_id', $session->id)
            ->whereIn('role', ['student', 'guest'])
            ->inRandomOrder()
            ->get();

        $assignments = [];

        foreach ($participants->values() as $index => $participant) {
            $room = $rooms[$index % $rooms->count()];
            $assignments[$room->id][] = $participant->id;
        }

        $this->assignParticipants($session, $assignments, 'manual');
    }
}
