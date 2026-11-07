<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $session_id
 * @property string $provider
 * @property string $event_type
 * @property array<string, mixed>|null $payload
 * @property Carbon|null $processed_at
 */
class ProviderEvent extends Model
{
    protected $table = 'classbridge_provider_events';

    protected $fillable = [
        'session_id',
        'provider',
        'event_type',
        'payload',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassroomSession::class, 'session_id');
    }
}
