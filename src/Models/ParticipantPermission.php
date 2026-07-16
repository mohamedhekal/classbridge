<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantPermission extends Model
{
    protected $table = 'classbridge_participant_permissions';

    protected $fillable = [
        'participant_id',
        'key',
        'granted',
    ];

    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'participant_id');
    }
}
