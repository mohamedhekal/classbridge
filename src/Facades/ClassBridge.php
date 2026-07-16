<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Facades;

use Hekal\ClassBridge\ClassBridgeManager;
use Hekal\ClassBridge\Contracts\ClassroomProvider;
use Hekal\ClassBridge\Services\AnalyticsService;
use Hekal\ClassBridge\Services\AttendanceService;
use Hekal\ClassBridge\Services\BreakoutService;
use Hekal\ClassBridge\Services\FeatureManifest;
use Hekal\ClassBridge\Services\JoinTokenService;
use Hekal\ClassBridge\Services\ModerationService;
use Hekal\ClassBridge\Services\PollService;
use Hekal\ClassBridge\Services\QuestionService;
use Hekal\ClassBridge\Services\RecordingService;
use Hekal\ClassBridge\Services\SessionService;
use Hekal\ClassBridge\Services\WaitingRoomService;
use Hekal\ClassBridge\Services\WhiteboardService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ClassBridgeManager forTenant(string|int|null $tenantId)
 * @method static ClassroomProvider driver(?string $name = null)
 * @method static SessionService session()
 * @method static WaitingRoomService waitingRoom()
 * @method static JoinTokenService joinTokens()
 * @method static ModerationService moderation()
 * @method static AttendanceService attendance()
 * @method static RecordingService recording()
 * @method static FeatureManifest features()
 * @method static PollService polls()
 * @method static QuestionService questions()
 * @method static BreakoutService breakouts()
 * @method static WhiteboardService whiteboard()
 * @method static AnalyticsService analytics()
 *
 * @see ClassBridgeManager
 */
final class ClassBridge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'classbridge';
    }
}
