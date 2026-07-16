<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Models\ClassroomSession;
use Hekal\ClassBridge\Models\Question;
use Hekal\ClassBridge\Models\SessionParticipant;

final class QuestionService
{
    public function ask(ClassroomSession $session, SessionParticipant $participant, string $body): Question
    {
        return Question::query()->create([
            'session_id' => $session->id,
            'participant_id' => $participant->id,
            'body' => $body,
            'status' => 'pending',
            'meta' => [
                'upvotes' => 0,
                'pinned' => false,
            ],
        ]);
    }

    public function upvote(Question $question): Question
    {
        $meta = (array) ($question->meta ?? []);
        $meta['upvotes'] = (int) ($meta['upvotes'] ?? 0) + 1;
        $question->meta = $meta;
        $question->save();

        return $question;
    }

    public function markAnswered(Question $question): Question
    {
        $question->status = 'answered';
        $question->save();

        return $question;
    }

    public function pin(Question $question): Question
    {
        $meta = (array) ($question->meta ?? []);
        $meta['pinned'] = true;
        $question->meta = $meta;
        $question->save();

        return $question;
    }
}
