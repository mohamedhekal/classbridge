<?php

declare(strict_types=1);

use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Exceptions\ClassBridgeException;
use Hekal\ClassBridge\Facades\ClassBridge;

it('scopes session lookup to the active tenant', function () {
    $sessionA = ClassBridge::forTenant('tenant-a')->session()->create(new CreateSessionData(
        title: 'Tenant A session',
        tenantId: 'tenant-a',
    ));

    ClassBridge::forTenant('tenant-b')->session()->find($sessionA->id);
})->throws(ClassBridgeException::class);

it('allows lookup within the same tenant', function () {
    $session = ClassBridge::forTenant('tenant-a')->session()->create(new CreateSessionData(
        title: 'Tenant scoped session',
        tenantId: 'tenant-a',
    ));

    $found = ClassBridge::forTenant('tenant-a')->session()->find($session->id);

    expect($found->id)->toBe($session->id);
});

it('creates sessions with tenant ids when multi-tenant is enabled', function () {
    $session = ClassBridge::forTenant('org-42')->session()->create(new CreateSessionData(
        title: 'Org session',
        tenantId: 'org-42',
    ));

    expect($session->tenant_id)->toBe('org-42');
});
