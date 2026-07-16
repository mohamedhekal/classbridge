<!DOCTYPE html>
<html lang="{{ config('classbridge.branding.locale', 'en') }}" dir="{{ config('classbridge.branding.direction', 'ltr') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $session->title }} — ClassBridge</title>
    <style>
        :root {
            --cb-primary: {{ $branding['primary_color'] ?? '#1FA6A0' }};
            --cb-bg: {{ $branding['classroom_background'] ?? '#0E1A24' }};
        }
        * { box-sizing: border-box; margin: 0; }
        body {
            font-family: system-ui, sans-serif;
            background: var(--cb-bg);
            color: #f5f7fa;
            min-height: 100vh;
            display: grid;
            place-items: center;
        }
        .embed-shell {
            width: min(960px, 100%);
            padding: 2rem;
            text-align: center;
        }
        .embed-shell h1 { font-size: 1.25rem; margin-bottom: .5rem; }
        .embed-shell p { opacity: .75; font-size: .9rem; }
        .badge {
            display: inline-block;
            margin-top: 1rem;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: var(--cb-primary);
            font-size: .75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="embed-shell" data-session-id="{{ $session->id }}">
        <h1>{{ $session->title }}</h1>
        <p>ClassBridge embed room — mount your client SDK or web component here.</p>
        <span class="badge">{{ strtoupper($session->status->value) }}</span>
    </div>
</body>
</html>
