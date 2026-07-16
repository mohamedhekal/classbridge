<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classbridge_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id')->nullable()->index();
            $table->string('title');
            $table->string('status')->index();
            $table->nullableMorphs('hostable');
            $table->nullableMorphs('courseable');
            $table->nullableMorphs('owner');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('provider')->default('fake');
            $table->string('provider_room_id')->nullable()->index();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('classbridge_session_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->json('features')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('classbridge_session_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->nullableMorphs('user');
            $table->string('role');
            $table->string('display_name');
            $table->string('status')->default('invited');
            $table->string('provider_participant_id')->nullable();
            $table->boolean('can_publish_audio')->default(false);
            $table->boolean('can_publish_video')->default(false);
            $table->boolean('can_publish_screen')->default(false);
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();
        });

        Schema::create('classbridge_participant_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('participant_id')->constrained('classbridge_session_participants')->cascadeOnDelete();
            $table->string('key');
            $table->boolean('granted')->default(false);
            $table->timestamps();

            $table->unique(['participant_id', 'key']);
        });

        Schema::create('classbridge_waiting_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->foreignUuid('participant_id')->nullable()->constrained('classbridge_session_participants')->nullOnDelete();
            $table->string('display_name');
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('classbridge_join_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->foreignUuid('participant_id')->constrained('classbridge_session_participants')->cascadeOnDelete();
            $table->string('token_hash');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->boolean('one_time')->default(true);
            $table->timestamps();

            $table->index('token_hash');
        });

        Schema::create('classbridge_attendance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->foreignUuid('participant_id')->constrained('classbridge_session_participants')->cascadeOnDelete();
            $table->string('event');
            $table->timestamp('occurred_at');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('classbridge_moderation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->foreignUuid('actor_participant_id')->nullable()->constrained('classbridge_session_participants')->nullOnDelete();
            $table->foreignUuid('target_participant_id')->nullable()->constrained('classbridge_session_participants')->nullOnDelete();
            $table->string('action');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('classbridge_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->foreignUuid('participant_id')->constrained('classbridge_session_participants')->cascadeOnDelete();
            $table->text('body');
            $table->string('type')->default('chat');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('classbridge_hand_raises', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->foreignUuid('participant_id')->constrained('classbridge_session_participants')->cascadeOnDelete();
            $table->timestamp('raised_at');
            $table->timestamp('lowered_at')->nullable();
            $table->string('status')->default('raised');
            $table->timestamps();
        });

        Schema::create('classbridge_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->string('provider_recording_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('url')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('classbridge_provider_events', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->nullable()->constrained('classbridge_sessions')->nullOnDelete();
            $table->string('provider');
            $table->string('event_type');
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('classbridge_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->foreignUuid('session_id')->nullable()->constrained('classbridge_sessions')->nullOnDelete();
            $table->nullableMorphs('actor');
            $table->string('action');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('classbridge_polls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->string('question');
            $table->string('status')->default('draft');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('classbridge_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('poll_id')->constrained('classbridge_polls')->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('classbridge_poll_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('poll_id')->constrained('classbridge_polls')->cascadeOnDelete();
            $table->foreignUuid('participant_id')->constrained('classbridge_session_participants')->cascadeOnDelete();
            $table->unsignedBigInteger('poll_option_id');
            $table->foreign('poll_option_id')->references('id')->on('classbridge_poll_options')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['poll_id', 'participant_id']);
        });

        Schema::create('classbridge_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->foreignUuid('participant_id')->constrained('classbridge_session_participants')->cascadeOnDelete();
            $table->text('body');
            $table->string('status')->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('classbridge_breakout_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->string('name');
            $table->string('provider_room_id')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('classbridge_breakout_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('breakout_room_id')->constrained('classbridge_breakout_rooms')->cascadeOnDelete();
            $table->foreignUuid('participant_id')->constrained('classbridge_session_participants')->cascadeOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['breakout_room_id', 'participant_id']);
        });

        Schema::create('classbridge_whiteboards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained('classbridge_sessions')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('classbridge_whiteboard_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('whiteboard_id')->constrained('classbridge_whiteboards')->cascadeOnDelete();
            $table->unsignedInteger('page_number')->default(1);
            $table->json('content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classbridge_whiteboard_pages');
        Schema::dropIfExists('classbridge_whiteboards');
        Schema::dropIfExists('classbridge_breakout_participants');
        Schema::dropIfExists('classbridge_breakout_rooms');
        Schema::dropIfExists('classbridge_questions');
        Schema::dropIfExists('classbridge_poll_responses');
        Schema::dropIfExists('classbridge_poll_options');
        Schema::dropIfExists('classbridge_polls');
        Schema::dropIfExists('classbridge_audit_logs');
        Schema::dropIfExists('classbridge_provider_events');
        Schema::dropIfExists('classbridge_recordings');
        Schema::dropIfExists('classbridge_hand_raises');
        Schema::dropIfExists('classbridge_messages');
        Schema::dropIfExists('classbridge_moderation_actions');
        Schema::dropIfExists('classbridge_attendance_events');
        Schema::dropIfExists('classbridge_join_tokens');
        Schema::dropIfExists('classbridge_waiting_entries');
        Schema::dropIfExists('classbridge_participant_permissions');
        Schema::dropIfExists('classbridge_session_participants');
        Schema::dropIfExists('classbridge_session_settings');
        Schema::dropIfExists('classbridge_sessions');
    }
};
