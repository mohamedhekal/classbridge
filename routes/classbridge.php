<?php

declare(strict_types=1);

use Hekal\ClassBridge\Http\Controllers\Api\AnalyticsController;
use Hekal\ClassBridge\Http\Controllers\Api\AttendanceController;
use Hekal\ClassBridge\Http\Controllers\Api\BreakoutController;
use Hekal\ClassBridge\Http\Controllers\Api\JoinController;
use Hekal\ClassBridge\Http\Controllers\Api\ModerationController;
use Hekal\ClassBridge\Http\Controllers\Api\PollController;
use Hekal\ClassBridge\Http\Controllers\Api\QuestionController;
use Hekal\ClassBridge\Http\Controllers\Api\RecordingController;
use Hekal\ClassBridge\Http\Controllers\Api\SessionController;
use Hekal\ClassBridge\Http\Controllers\Api\WebhookController;
use Hekal\ClassBridge\Http\Controllers\Api\WhiteboardController;
use Illuminate\Support\Facades\Route;

Route::post('sessions', [SessionController::class, 'store']);
Route::get('sessions/{session}', [SessionController::class, 'show']);
Route::post('sessions/{session}/schedule', [SessionController::class, 'schedule']);
Route::post('sessions/{session}/waiting', [SessionController::class, 'openWaiting']);
Route::post('sessions/{session}/start', [SessionController::class, 'start']);
Route::post('sessions/{session}/end', [SessionController::class, 'end']);

Route::post('join', [JoinController::class, 'join']);
Route::post('join/validate', [JoinController::class, 'validateToken']);

Route::post('sessions/{session}/moderation/mute', [ModerationController::class, 'mute']);
Route::post('sessions/{session}/moderation/remove', [ModerationController::class, 'remove']);

Route::post('sessions/{session}/attendance/finalize', [AttendanceController::class, 'finalize']);

Route::post('sessions/{session}/recordings/start', [RecordingController::class, 'start']);
Route::post('sessions/{session}/recordings/{recording}/stop', [RecordingController::class, 'stop']);
Route::post('sessions/{session}/recordings/{recording}/publish', [RecordingController::class, 'publish']);

Route::post('sessions/{session}/polls', [PollController::class, 'store']);
Route::post('sessions/{session}/polls/{poll}/start', [PollController::class, 'start']);
Route::post('sessions/{session}/polls/{poll}/close', [PollController::class, 'close']);
Route::post('sessions/{session}/polls/{poll}/respond', [PollController::class, 'respond']);
Route::get('sessions/{session}/polls/{poll}/results', [PollController::class, 'results']);

Route::post('sessions/{session}/questions', [QuestionController::class, 'store']);
Route::post('sessions/{session}/questions/{question}/upvote', [QuestionController::class, 'upvote']);
Route::post('sessions/{session}/questions/{question}/answered', [QuestionController::class, 'markAnswered']);
Route::post('sessions/{session}/questions/{question}/pin', [QuestionController::class, 'pin']);

Route::post('sessions/{session}/breakouts', [BreakoutController::class, 'createRooms']);
Route::post('sessions/{session}/breakouts/assign', [BreakoutController::class, 'assign']);
Route::post('sessions/{session}/breakouts/open', [BreakoutController::class, 'open']);
Route::post('sessions/{session}/breakouts/close', [BreakoutController::class, 'closeAll']);
Route::post('sessions/{session}/breakouts/broadcast', [BreakoutController::class, 'broadcast']);

Route::post('sessions/{session}/whiteboards', [WhiteboardController::class, 'store']);
Route::post('sessions/{session}/whiteboards/{whiteboard}/pages', [WhiteboardController::class, 'addPage']);
Route::get('sessions/{session}/whiteboards/{whiteboard}/snapshot', [WhiteboardController::class, 'snapshot']);
Route::post('sessions/{session}/whiteboards/{whiteboard}/lock', [WhiteboardController::class, 'lock']);
Route::post('sessions/{session}/whiteboards/{whiteboard}/unlock', [WhiteboardController::class, 'unlock']);

Route::get('sessions/{session}/analytics/summary', [AnalyticsController::class, 'sessionSummary']);

Route::post('webhooks/{provider}', [WebhookController::class, 'handle']);
