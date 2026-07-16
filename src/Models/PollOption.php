<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    protected $table = 'classbridge_poll_options';

    protected $fillable = [
        'poll_id',
        'label',
        'sort_order',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class, 'poll_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PollResponse::class, 'poll_option_id');
    }
}
