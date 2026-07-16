<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\DTOs;

use Hekal\ClassBridge\Enums\RecordingStatus;

final readonly class RecordingResult
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $recordingId,
        public RecordingStatus $status,
        public ?string $url = null,
        public array $meta = [],
    ) {}
}
