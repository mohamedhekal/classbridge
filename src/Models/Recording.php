<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Hekal\ClassBridge\Enums\RecordingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $session_id
 * @property string|null $provider_recording_id
 * @property RecordingStatus $status
 * @property string|null $url
 * @property array<string, mixed>|null $meta
 */
class Recording extends Model
{
    protected $table = 'classbridge_recordings';

    protected $fillable = [
        'session_id',
        'provider_recording_id',
        'status',
        'url',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => RecordingStatus::class,
            'meta' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassroomSession::class, 'session_id');
    }
}
