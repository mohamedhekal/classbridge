<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreakoutRoom extends Model
{
    use HasUuids;

    protected $table = 'classbridge_breakout_rooms';

    protected $fillable = [
        'session_id',
        'name',
        'provider_room_id',
        'status',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassroomSession::class, 'session_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(BreakoutParticipant::class, 'breakout_room_id');
    }
}
