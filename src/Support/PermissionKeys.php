<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Support;

final class PermissionKeys
{
    public const MUTE_OTHERS = 'classroom.mute_others';

    public const REMOVE_PARTICIPANTS = 'classroom.remove_participants';

    public const MANAGE_WAITING_ROOM = 'classroom.manage_waiting_room';

    public const START_RECORDING = 'classroom.start_recording';

    public const MANAGE_BREAKOUTS = 'classroom.manage_breakouts';

    public const PUBLISH_AUDIO = 'classroom.publish_audio';

    public const PUBLISH_VIDEO = 'classroom.publish_video';

    public const PUBLISH_SCREEN = 'classroom.publish_screen';

    public const SEND_CHAT = 'classroom.send_chat';

    public const RAISE_HAND = 'classroom.raise_hand';

    /**
     * @return list<string>
     */
    public static function defaultsForRole(string $role): array
    {
        return match ($role) {
            'teacher', 'cohost' => [
                self::MUTE_OTHERS,
                self::REMOVE_PARTICIPANTS,
                self::MANAGE_WAITING_ROOM,
                self::START_RECORDING,
                self::MANAGE_BREAKOUTS,
                self::PUBLISH_AUDIO,
                self::PUBLISH_VIDEO,
                self::PUBLISH_SCREEN,
                self::SEND_CHAT,
                self::RAISE_HAND,
            ],
            'student' => [
                self::PUBLISH_AUDIO,
                self::PUBLISH_VIDEO,
                self::SEND_CHAT,
                self::RAISE_HAND,
            ],
            default => [
                self::SEND_CHAT,
            ],
        };
    }
}
