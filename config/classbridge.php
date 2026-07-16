<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Classroom Provider
    |--------------------------------------------------------------------------
    */
    'default' => env('CLASSBRIDGE_DRIVER', 'fake'),

    'drivers' => [
        'fake' => [
            'driver' => 'fake',
        ],
        'livekit' => [
            'driver' => 'livekit',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Moderation Privacy Defaults
    |--------------------------------------------------------------------------
    */
    'moderation' => [
        'teacher_can_mute' => true,
        'teacher_can_request_unmute' => true,
        'teacher_can_force_unmute' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Attendance Rules
    |--------------------------------------------------------------------------
    */
    'attendance' => [
        'minimum_percentage' => 75,
        'late_after_minutes' => 15,
        'ignore_disconnects_under_seconds' => 30,
        'require_quiz_submission' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Manifest Defaults
    |--------------------------------------------------------------------------
    */
    'features' => [
        'video' => true,
        'audio' => true,
        'screen_share' => true,
        'whiteboard' => true,
        'chat' => true,
        'private_chat' => false,
        'polls' => true,
        'quizzes' => false,
        'breakout_rooms' => true,
        'recording' => true,
        'transcription' => false,
        'ai_summary' => false,
        'student_camera_default' => false,
        'student_microphone_default' => false,
        'waiting_room' => true,
        'join_before_teacher' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Isolation
    |--------------------------------------------------------------------------
    */
    'multi_tenant' => [
        'enabled' => env('CLASSBRIDGE_MULTI_TENANT', true),
        'column' => 'tenant_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Join Tokens
    |--------------------------------------------------------------------------
    */
    'tokens' => [
        'ttl_seconds' => (int) env('CLASSBRIDGE_TOKEN_TTL', 3600),
        'one_time' => true,
        'signing_key' => env('CLASSBRIDGE_TOKEN_KEY', env('APP_KEY')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding (host platform overrides)
    |--------------------------------------------------------------------------
    */
    'branding' => [
        'logo' => null,
        'primary_color' => '#1FA6A0',
        'secondary_color' => '#E8A54B',
        'classroom_background' => '#0E1A24',
        'direction' => 'ltr',
        'locale' => 'en',
    ],

    /*
    |--------------------------------------------------------------------------
    | Outbound Webhooks
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'enabled' => env('CLASSBRIDGE_WEBHOOKS', false),
        'signing_secret' => env('CLASSBRIDGE_WEBHOOK_SECRET'),
        'tolerance_seconds' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP API
    |--------------------------------------------------------------------------
    */
    'api' => [
        'enabled' => env('CLASSBRIDGE_API', true),
        'prefix' => 'api/classbridge',
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Join URL Templates
    |--------------------------------------------------------------------------
    */
    'urls' => [
        'join' => env('CLASSBRIDGE_JOIN_URL', '/classroom/{session}'),
        'host' => env('CLASSBRIDGE_HOST_URL', '/classroom/{session}/host'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Iframe Embed Mode
    |--------------------------------------------------------------------------
    */
    'embed' => [
        'enabled' => env('CLASSBRIDGE_EMBED', false),
        'prefix' => 'embed/classbridge',
        'middleware' => ['web'],
    ],
];
