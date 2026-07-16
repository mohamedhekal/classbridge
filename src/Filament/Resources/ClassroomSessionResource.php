<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Filament\Resources;

/**
 * Optional Filament admin resource stub.
 *
 * This class is only registered when Filament is installed in the host application.
 * Add to your AppServiceProvider:
 *
 *   if (class_exists(\Filament\Resources\Resource::class)) {
 *       // register ClassroomSessionResource manually or via package discovery
 *   }
 */
final class ClassroomSessionResource
{
    public static function isAvailable(): bool
    {
        return class_exists(\Filament\Resources\Resource::class);
    }
}
