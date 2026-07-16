<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $session_id
 * @property string $participant_id
 * @property string $token_hash
 * @property \Carbon\CarbonInterface $expires_at
 * @property \Carbon\CarbonInterface|null $used_at
 * @property bool $one_time
 * @property-read SessionParticipant|null $participant
 * @property-read ClassroomSession|null $session
 */
class JoinToken extends Model
{
    protected $table = 'classbridge_join_tokens';

    public ?string $plainToken = null;

    protected $fillable = [
        'session_id',
        'participant_id',
        'token_hash',
        'expires_at',
        'used_at',
        'one_time',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'one_time' => 'boolean',
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

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }
}
