<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $session_id
 * @property array<string, bool>|null $features
 * @property array<string, mixed>|null $settings
 */
class SessionSetting extends Model
{
    protected $table = 'classbridge_session_settings';

    protected $fillable = [
        'session_id',
        'features',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'settings' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassroomSession::class, 'session_id');
    }
}
