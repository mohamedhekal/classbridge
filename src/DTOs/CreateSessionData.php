<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\DTOs;

use Hekal\ClassBridge\Enums\ParticipantRole;
use Hekal\ClassBridge\Enums\SessionStatus;

final readonly class CreateSessionData
{
    /**
     * @param  array<string, mixed>|null  $hostable
     * @param  array<string, mixed>|null  $courseable
     * @param  array<string, mixed>|null  $owner
     * @param  array<string, bool>|null  $features
     */
    public function __construct(
        public string $title,
        public ?string $tenantId = null,
        public ?ParticipantRole $teacherRole = ParticipantRole::Teacher,
        public ?string $teacherDisplayName = 'Teacher',
        public ?string $teacherUserType = null,
        public int|string|null $teacherUserId = null,
        public ?array $hostable = null,
        public ?array $courseable = null,
        public ?array $owner = null,
        public ?\DateTimeInterface $startsAt = null,
        public ?\DateTimeInterface $endsAt = null,
        public ?int $durationMinutes = null,
        public SessionStatus $initialStatus = SessionStatus::Draft,
        public ?array $features = null,
        public ?string $provider = null,
    ) {}
}
