<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Services;

use Hekal\ClassBridge\Exceptions\ClassBridgeException;
use Hekal\ClassBridge\Models\JoinToken;
use Hekal\ClassBridge\Models\SessionParticipant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class JoinTokenService
{
    public function issue(SessionParticipant $participant, ?int $ttlMinutes = null, ?bool $oneTime = null): JoinToken
    {
        $ttlMinutes ??= (int) config('classbridge.tokens.ttl_minutes', 60);
        $oneTime ??= (bool) config('classbridge.tokens.one_time', true);

        $plain = $this->generatePlainToken($participant);
        $hash = $this->hashToken($plain);

        $token = JoinToken::query()->create([
            'session_id' => $participant->session_id,
            'participant_id' => $participant->id,
            'token_hash' => $hash,
            'expires_at' => now()->addMinutes($ttlMinutes),
            'one_time' => $oneTime,
        ]);

        $token->plainToken = $plain;

        return $token;
    }

    public function validate(string $plainToken): JoinToken
    {
        $hash = $this->hashToken($plainToken);

        $token = JoinToken::query()
            ->where('token_hash', $hash)
            ->with(['participant', 'session'])
            ->first();

        if ($token === null) {
            throw ClassBridgeException::invalidToken();
        }

        if ($token->isExpired()) {
            throw ClassBridgeException::invalidToken('Join token has expired.');
        }

        if ($token->one_time && $token->isUsed()) {
            throw ClassBridgeException::invalidToken('Join token has already been used.');
        }

        return $token;
    }

    public function consume(JoinToken $token): JoinToken
    {
        if ($token->one_time) {
            $token->used_at = Carbon::now();
            $token->save();
        }

        return $token;
    }

    private function generatePlainToken(SessionParticipant $participant): string
    {
        $payload = implode('|', [
            $participant->session_id,
            $participant->id,
            Str::random(32),
            (string) now()->timestamp,
        ]);

        $key = (string) config('classbridge.tokens.signing_key', config('app.key'));

        return hash_hmac('sha256', $payload, $key).'.'.base64_encode($payload);
    }

    private function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
