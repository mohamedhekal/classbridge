<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Whiteboard extends Model
{
    use HasUuids;

    protected $table = 'classbridge_whiteboards';

    protected $fillable = [
        'session_id',
        'title',
        'status',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassroomSession::class, 'session_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(WhiteboardPage::class, 'whiteboard_id');
    }
}
