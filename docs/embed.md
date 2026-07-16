# Iframe embed mode

ClassBridge can serve a minimal embed shell for LMS and portal integrations.

## Enable embed routes

```env
CLASSBRIDGE_EMBED=true
```

Config (`config/classbridge.php`):

```php
'embed' => [
    'enabled' => env('CLASSBRIDGE_EMBED', false),
    'prefix' => 'embed/classbridge',
    'middleware' => ['web'],
],
```

## Embed URL

```
https://your-app.test/embed/classbridge/room/{session_uuid}
```

The view at `resources/views/embed/room.blade.php` renders a lightweight shell. Replace the placeholder with:

- `@classbridge/whiteboard` for collaborative boards
- `classbridge-room` web component for the full stage
- Your Vue/Inertia host page loaded inside the iframe

## iframe snippet

```html
<iframe
  src="https://your-app.test/embed/classbridge/room/SESSION_UUID"
  title="Classroom"
  allow="camera; microphone; display-capture; fullscreen"
  style="width:100%;height:600px;border:0;border-radius:12px;"
></iframe>
```

## Security

- Issue short-lived join tokens server-side; pass via postMessage rather than query strings when possible.
- Set `Content-Security-Policy` frame-ancestors to trusted LMS domains.
- Keep `CLASSBRIDGE_EMBED` disabled unless iframe hosting is required.

## Branding

Embed pages inherit `config('classbridge.branding')` colors and locale/direction.
