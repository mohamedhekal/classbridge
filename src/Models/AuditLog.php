<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'classbridge_audit_logs';

    protected $fillable = [
        'tenant_id',
        'session_id',
        'actor_type',
        'actor_id',
        'action',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassroomSession::class, 'session_id');
    }
}
