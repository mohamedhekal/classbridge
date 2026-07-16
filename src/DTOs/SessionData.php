<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\DTOs;

use Hekal\ClassBridge\Enums\SessionStatus;

final readonly class SessionData
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $id,
        public string $title,
        public SessionStatus $status,
        public ?string $tenantId = null,
        public ?string $providerRoomId = null,
        public ?string $provider = null,
        public array $meta = [],
    ) {}
}
