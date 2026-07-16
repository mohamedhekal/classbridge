<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakoutParticipant extends Model
{
    protected $table = 'classbridge_breakout_participants';

    protected $fillable = [
        'breakout_room_id',
        'participant_id',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    public function breakoutRoom(): BelongsTo
    {
        return $this->belongsTo(BreakoutRoom::class, 'breakout_room_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'participant_id');
    }
}
