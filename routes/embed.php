<?php

declare(strict_types=1);

use Hekal\ClassBridge\Http\Controllers\EmbedController;
use Illuminate\Support\Facades\Route;

Route::get('room/{session}', [EmbedController::class, 'room'])->name('classbridge.embed.room');
