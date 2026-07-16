<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\DTOs;

final readonly class ParticipantPermissions
{
    public function __construct(
        public bool $canPublishAudio = false,
        public bool $canPublishVideo = false,
        public bool $canPublishScreen = false,
    ) {}

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'can_publish_audio' => $this->canPublishAudio,
            'can_publish_video' => $this->canPublishVideo,
            'can_publish_screen' => $this->canPublishScreen,
        ];
    }

    /**
     * @param  array<string, bool>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            canPublishAudio: (bool) ($data['can_publish_audio'] ?? false),
            canPublishVideo: (bool) ($data['can_publish_video'] ?? false),
            canPublishScreen: (bool) ($data['can_publish_screen'] ?? false),
        );
    }
}
