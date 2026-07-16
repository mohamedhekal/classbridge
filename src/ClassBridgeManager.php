<?php

declare(strict_types=1);

namespace Hekal\ClassBridge;

use Closure;
use Hekal\ClassBridge\Contracts\ClassroomProvider;
use Hekal\ClassBridge\Drivers\FakeClassroomDriver;
use Hekal\ClassBridge\Exceptions\ClassBridgeException;
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
use Hekal\ClassBridge\Services\SessionStateMachine;
use Hekal\ClassBridge\Services\WaitingRoomService;
use Hekal\ClassBridge\Services\WhiteboardService;
use Illuminate\Contracts\Container\Container;

final class ClassBridgeManager
{
    /** @var array<string, ClassroomProvider> */
    private array $resolved = [];

    /** @var array<string, Closure(Container, array<string, mixed>): ClassroomProvider> */
    private array $customCreators = [];

    private ?string $tenantId = null;

    public function __construct(
        private readonly Container $container,
    ) {}

    public function forTenant(string|int|null $tenantId): self
    {
        $clone = clone $this;
        $clone->tenantId = $tenantId !== null ? (string) $tenantId : null;

        return $clone;
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
    }

    public function driver(?string $name = null): ClassroomProvider
    {
        $name ??= (string) config('classbridge.default', 'fake');

        return $this->resolved[$name] ??= $this->resolve($name);
    }

    /**
     * @param  Closure(Container, array<string, mixed>): ClassroomProvider  $callback
     */
    public function extend(string $name, Closure $callback): void
    {
        $this->customCreators[$name] = $callback;
        unset($this->resolved[$name]);
    }

    public function session(): SessionService
    {
        return new SessionService(
            provider: $this->driver(),
            stateMachine: $this->container->make(SessionStateMachine::class),
            featureManifest: $this->container->make(FeatureManifest::class),
            joinTokenService: $this->container->make(JoinTokenService::class),
            tenantId: $this->tenantId,
        );
    }

    public function waitingRoom(): WaitingRoomService
    {
        return $this->container->make(WaitingRoomService::class);
    }

    public function joinTokens(): JoinTokenService
    {
        return $this->container->make(JoinTokenService::class);
    }

    public function moderation(): ModerationService
    {
        return new ModerationService($this->driver());
    }

    public function attendance(): AttendanceService
    {
        return $this->container->make(AttendanceService::class);
    }

    public function recording(): RecordingService
    {
        return new RecordingService($this->driver());
    }

    public function features(): FeatureManifest
    {
        return $this->container->make(FeatureManifest::class);
    }

    public function polls(): PollService
    {
        return $this->container->make(PollService::class);
    }

    public function questions(): QuestionService
    {
        return $this->container->make(QuestionService::class);
    }

    public function breakouts(): BreakoutService
    {
        return new BreakoutService($this->driver());
    }

    public function whiteboard(): WhiteboardService
    {
        return $this->container->make(WhiteboardService::class);
    }

    public function analytics(): AnalyticsService
    {
        return $this->container->make(AnalyticsService::class);
    }

    private function resolve(string $name): ClassroomProvider
    {
        /** @var array<string, mixed>|null $config */
        $config = config("classbridge.drivers.{$name}");

        if (! is_array($config)) {
            throw ClassBridgeException::unknownDriver($name);
        }

        $type = (string) ($config['driver'] ?? $name);

        if (isset($this->customCreators[$type])) {
            return ($this->customCreators[$type])($this->container, $config);
        }

        return match ($type) {
            'fake' => new FakeClassroomDriver,
            default => throw ClassBridgeException::unsupportedDriver($type),
        };
    }
}
