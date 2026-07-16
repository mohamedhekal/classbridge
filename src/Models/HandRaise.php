<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandRaise extends Model
{
    protected $table = 'classbridge_hand_raises';

    protected $fillable = [
        'session_id',
        'participant_id',
        'raised_at',
        'lowered_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'raised_at' => 'datetime',
            'lowered_at' => 'datetime',
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
