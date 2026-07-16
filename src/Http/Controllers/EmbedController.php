<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Http\Controllers;

use Hekal\ClassBridge\Models\ClassroomSession;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class EmbedController extends Controller
{
    public function room(string $session): View
    {
        $sessionModel = ClassroomSession::query()->findOrFail($session);

        return view('classbridge::embed.room', [
            'session' => $sessionModel,
            'branding' => config('classbridge.branding', []),
        ]);
    }
}
