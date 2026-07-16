<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhiteboardPage extends Model
{
    protected $table = 'classbridge_whiteboard_pages';

    protected $fillable = [
        'whiteboard_id',
        'page_number',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    public function whiteboard(): BelongsTo
    {
        return $this->belongsTo(Whiteboard::class, 'whiteboard_id');
    }
}
