<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Carbon\CarbonInterface;
use Hekal\ClassBridge\Enums\ParticipantRole;
use Hekal\ClassBridge\Enums\ParticipantStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $session_id
 * @property string|null $tenant_id
 * @property string|null $user_type
 * @property int|string|null $user_id
 * @property ParticipantRole $role
 * @property string $display_name
 * @property ParticipantStatus $status
 * @property string|null $provider_participant_id
 * @property bool $can_publish_audio
 * @property bool $can_publish_video
 * @property bool $can_publish_screen
 * @property CarbonInterface|null $joined_at
 * @property CarbonInterface|null $left_at
 */
class SessionParticipant extends Model
{
    use HasUuids;

    protected $table = 'classbridge_session_participants';

    protected $fillable = [
        'session_id',
        'tenant_id',
        'user_type',
        'user_id',
        'role',
        'display_name',
        'status',
        'provider_participant_id',
        'can_publish_audio',
        'can_publish_video',
        'can_publish_screen',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => ParticipantRole::class,
            'status' => ParticipantStatus::class,
            'can_publish_audio' => 'boolean',
            'can_publish_video' => 'boolean',
            'can_publish_screen' => 'boolean',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassroomSession::class, 'session_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(ParticipantPermission::class, 'participant_id');
    }

    public function joinTokens(): HasMany
    {
        return $this->hasMany(JoinToken::class, 'participant_id');
    }

    public function canModerate(): bool
    {
        return $this->role->canModerate();
    }
}
