<?php

declare(strict_types=1);

use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Exceptions\ClassBridgeException;
use Hekal\ClassBridge\Facades\ClassBridge;
use Hekal\ClassBridge\Models\JoinToken;

it('issues and validates join tokens', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Token test'));
    $teacher = $session->participants->first();

    $token = ClassBridge::joinTokens()->issue($teacher);

    expect($token->plainToken)->not->toBeNull()
        ->and($token->expires_at->isFuture())->toBeTrue();

    $validated = ClassBridge::joinTokens()->validate($token->plainToken);

    expect($validated->id)->toBe($token->id);
});

it('consumes one-time tokens', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Consume test'));
    $teacher = $session->participants->first();

    $token = ClassBridge::joinTokens()->issue($teacher);
    ClassBridge::joinTokens()->consume($token);

    expect($token->fresh()->used_at)->not->toBeNull();

    ClassBridge::joinTokens()->validate($token->plainToken);
})->throws(ClassBridgeException::class);

it('stores hashed tokens in the database', function () {
    $session = ClassBridge::session()->create(new CreateSessionData(title: 'Hash test'));
    $teacher = $session->participants->first();

    $token = ClassBridge::joinTokens()->issue($teacher);

    $stored = JoinToken::query()->find($token->id);

    expect($stored->token_hash)->not->toBe($token->plainToken)
        ->and(strlen($stored->token_hash))->toBe(64);
});
