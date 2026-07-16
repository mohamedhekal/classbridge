<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\DTOs;

final readonly class ProviderRoom
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $roomId,
        public string $provider,
        public array $meta = [],
    ) {}
}
