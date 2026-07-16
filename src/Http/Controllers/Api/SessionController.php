<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers\Api;

use Hekal\ClassBridge\ClassBridgeManager;
use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Models\ClassroomSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class SessionController extends Controller
{
    public function __construct(
        private readonly ClassBridgeManager $classBridge,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'tenant_id' => ['nullable', 'string'],
            'teacher_display_name' => ['nullable', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]);

        $manager = $validated['tenant_id'] ?? null
            ? $this->classBridge->forTenant($validated['tenant_id'])
            : $this->classBridge;

        $session = $manager->session()->create(new CreateSessionData(
            title: $validated['title'],
            tenantId: $validated['tenant_id'] ?? null,
            teacherDisplayName: $validated['teacher_display_name'] ?? 'Teacher',
            startsAt: isset($validated['starts_at']) ? new \DateTimeImmutable($validated['starts_at']) : null,
            endsAt: isset($validated['ends_at']) ? new \DateTimeImmutable($validated['ends_at']) : null,
        ));

        return response()->json([
            'data' => $this->formatSession($session),
        ], 201);
    }

    public function show(string $session): JsonResponse
    {
        $model = ClassroomSession::query()->with(['settings', 'participants'])->findOrFail($session);

        return response()->json(['data' => $this->formatSession($model)]);
    }

    public function schedule(string $session, Request $request): JsonResponse
    {
        $model = ClassroomSession::query()->findOrFail($session);
        $validated = $request->validate([
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]);

        $updated = $this->classBridge->session()->schedule(
            $model,
            isset($validated['starts_at']) ? new \DateTimeImmutable($validated['starts_at']) : null,
            isset($validated['ends_at']) ? new \DateTimeImmutable($validated['ends_at']) : null,
        );

        return response()->json(['data' => $this->formatSession($updated)]);
    }

    public function openWaiting(string $session): JsonResponse
    {
        $model = ClassroomSession::query()->findOrFail($session);
        $updated = $this->classBridge->session()->openWaiting($model);

        return response()->json(['data' => $this->formatSession($updated)]);
    }

    public function start(string $session): JsonResponse
    {
        $model = ClassroomSession::query()->findOrFail($session);
        $updated = $this->classBridge->session()->start($model);

        return response()->json(['data' => $this->formatSession($updated)]);
    }

    public function end(string $session): JsonResponse
    {
        $model = ClassroomSession::query()->findOrFail($session);
        $updated = $this->classBridge->session()->end($model);

        return response()->json(['data' => $this->formatSession($updated)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatSession(ClassroomSession $session): array
    {
        return [
            'id' => $session->id,
            'title' => $session->title,
            'status' => $session->status->value,
            'tenant_id' => $session->tenant_id,
            'provider_room_id' => $session->provider_room_id,
            'join_url' => $session->joinUrl(),
            'starts_at' => $session->starts_at?->toIso8601String(),
            'ends_at' => $session->ends_at?->toIso8601String(),
            'features' => $session->settings?->features,
        ];
    }
}
