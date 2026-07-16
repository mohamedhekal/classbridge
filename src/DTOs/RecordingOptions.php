<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\DTOs;

final readonly class RecordingOptions
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public bool $audioOnly = false,
        public ?string $layout = null,
        public array $meta = [],
    ) {}
}
