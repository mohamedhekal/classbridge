<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Events\PollStarted;
use Hekal\ClassBridge\Exceptions\ClassBridgeException;
use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\Poll;
use Hekal\ClassBridge\Models\PollOption;
use Hekal\ClassBridge\Models\PollResponse;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class PollService
{
    /**
     * @param  list<string>  $options
     */
    public function createPoll(ClassroomSession $session, string $question, array $options): Poll
    {
        return DB::transaction(function () use ($session, $question, $options): Poll {
            $poll = Poll::query()->create([
                'session_id' => $session->id,
                'question' => $question,
                'status' => 'draft',
            ]);

            foreach (array_values($options) as $index => $label) {
                PollOption::query()->create([
                    'poll_id' => $poll->id,
                    'label' => $label,
                    'sort_order' => $index,
                ]);
            }

            return $poll->load('options');
        });
    }

    public function start(Poll $poll): Poll
    {
        if ($poll->status === 'closed') {
            throw ClassBridgeException::invalidPollState('Cannot start a closed poll.');
        }

        $poll->status = 'active';
        $poll->started_at = now();
        $poll->ended_at = null;
        $poll->save();

        Event::dispatch(new PollStarted($poll));

        return $poll->fresh(['options']);
    }

    public function close(Poll $poll): Poll
    {
        $poll->status = 'closed';
        $poll->ended_at = now();
        $poll->save();

        return $poll->fresh(['options']);
    }

    public function respond(Poll $poll, SessionParticipant $participant, int $pollOptionId): PollResponse
    {
        if ($poll->status !== 'active') {
            throw ClassBridgeException::invalidPollState('Poll is not active.');
        }

        $option = PollOption::query()
            ->where('poll_id', $poll->id)
            ->whereKey($pollOptionId)
            ->first();

        if ($option === null) {
            throw ClassBridgeException::pollOptionNotFound($pollOptionId);
        }

        return PollResponse::query()->updateOrCreate(
            [
                'poll_id' => $poll->id,
                'participant_id' => $participant->id,
            ],
            [
                'poll_option_id' => $pollOptionId,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function results(Poll $poll): array
    {
        $poll->load(['options.responses']);

        $totalResponses = $poll->responses()->count();

        $options = $poll->options->map(function (PollOption $option) use ($totalResponses): array {
            $count = $option->responses()->count();

            return [
                'id' => $option->id,
                'label' => $option->label,
                'count' => $count,
                'percentage' => $totalResponses > 0 ? round(($count / $totalResponses) * 100, 1) : 0.0,
            ];
        })->values()->all();

        return [
            'poll_id' => $poll->id,
            'question' => $poll->question,
            'status' => $poll->status,
            'total_responses' => $totalResponses,
            'options' => $options,
        ];
    }
}
