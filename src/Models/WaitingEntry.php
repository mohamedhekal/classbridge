<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $session_id
 * @property string|null $participant_id
 * @property string $display_name
 * @property string $status
 * @property array<string, mixed>|null $metadata
 * @property-read SessionParticipant|null $participant
 */
class WaitingEntry extends Model
{
    protected $table = 'classbridge_waiting_entries';

    protected $fillable = [
        'session_id',
        'participant_id',
        'display_name',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassroomSession::class, 'session_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'participant_id');
    }
}
