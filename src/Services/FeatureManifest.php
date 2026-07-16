<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

final class FeatureManifest
{
    /**
     * @param  array<string, bool>|null  $tenantFeatures
     * @param  array<string, bool>|null  $sessionFeatures
     * @return array<string, bool>
     */
    public function merge(?string $tenantId = null, ?array $tenantFeatures = null, ?array $sessionFeatures = null): array
    {
        $defaults = (array) config('classbridge.features', []);
        $merged = [];

        foreach ($defaults as $key => $value) {
            $merged[$key] = (bool) $value;
        }

        if ($tenantFeatures !== null) {
            foreach ($tenantFeatures as $key => $value) {
                $merged[$key] = (bool) $value;
            }
        }

        if ($sessionFeatures !== null) {
            foreach ($sessionFeatures as $key => $value) {
                $merged[$key] = (bool) $value;
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, bool>|null  $sessionFeatures
     */
    public function enabled(string $feature, ?string $tenantId = null, ?array $sessionFeatures = null): bool
    {
        /** @var array<string, bool> $merged */
        $merged = $this->merge($tenantId, null, $sessionFeatures);

        return $merged[$feature] ?? false;
    }
}
