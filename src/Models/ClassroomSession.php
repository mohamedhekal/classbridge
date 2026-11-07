<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Carbon\CarbonInterface;
use Hekal\ClassBridge\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string|null $tenant_id
 * @property string $title
 * @property SessionStatus $status
 * @property string|null $hostable_type
 * @property int|string|null $hostable_id
 * @property string|null $courseable_type
 * @property int|string|null $courseable_id
 * @property string|null $owner_type
 * @property int|string|null $owner_id
 * @property CarbonInterface|null $starts_at
 * @property CarbonInterface|null $ends_at
 * @property int|null $duration_minutes
 * @property string $provider
 * @property string|null $provider_room_id
 * @property CarbonInterface|null $locked_at
 * @property CarbonInterface|null $started_at
 * @property CarbonInterface|null $ended_at
 * @property-read SessionSetting|null $settings
 * @property-read Collection<int, SessionParticipant> $participants
 */
class ClassroomSession extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'classbridge_sessions';

    protected $fillable = [
        'tenant_id',
        'title',
        'status',
        'hostable_type',
        'hostable_id',
        'courseable_type',
        'courseable_id',
        'owner_type',
        'owner_id',
        'starts_at',
        'ends_at',
        'duration_minutes',
        'provider',
        'provider_room_id',
        'locked_at',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SessionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'locked_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function settings(): HasOne
    {
        return $this->hasOne(SessionSetting::class, 'session_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class, 'session_id');
    }

    public function waitingEntries(): HasMany
    {
        return $this->hasMany(WaitingEntry::class, 'session_id');
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(Recording::class, 'session_id');
    }

    public function joinUrl(?string $token = null): string
    {
        $template = (string) config('classbridge.branding.join_url_template', '/classroom/join/{token}');

        if ($token === null) {
            return str_replace('{token}', $this->id, $template);
        }

        return str_replace('{token}', $token, $template);
    }

    public function hostJoinUrl(): ?string
    {
        /** @var SessionParticipant|null $teacher */
        $teacher = $this->participants()
            ->where('role', 'teacher')
            ->first();

        if ($teacher === null) {
            return null;
        }

        $joinToken = $teacher->joinTokens()->latest()->first();

        return $joinToken !== null
            ? $this->joinUrl($joinToken->plainToken ?? '')
            : $this->joinUrl();
    }
}
