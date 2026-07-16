<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\DTOs;

final readonly class JoinCredentials
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $token,
        public string $roomId,
        public string $participantId,
        public ?string $url = null,
        public array $meta = [],
    ) {}
}
