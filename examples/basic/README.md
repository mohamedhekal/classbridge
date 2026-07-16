# Basic Example

Minimal flow for a single-tenant LMS integrating ClassBridge.

## 1. Install & migrate

```bash
composer require mohamedhekal/classbridge
php artisan vendor:publish --tag=classbridge-config
php artisan vendor:publish --tag=classbridge-migrations
php artisan migrate
```

## 2. Schedule a class

```php
use Hekal\ClassBridge\DTOs\CreateSessionData;
use Hekal\ClassBridge\Facades\ClassBridge;

$session = ClassBridge::session()->create(new CreateSessionData(
    title: 'Biology 101 — Cell Structure',
    teacherDisplayName: auth()->user()->name,
    teacherUserType: User::class,
    teacherUserId: auth()->id(),
    startsAt: now()->addDay(),
));

ClassBridge::session()->schedule($session, now()->addDay(), now()->addDay()->addHour());
```

## 3. Open waiting room before start

```php
ClassBridge::session()->openWaiting($session);
```

## 4. Admit students

```php
$student = $session->participants()->create([
    'role' => 'student',
    'display_name' => 'Jane Doe',
    'status' => 'invited',
]);

$entry = ClassBridge::waitingRoom()->enter($session, $student);
ClassBridge::waitingRoom()->admit($session, $entry);
```

## 5. Start & end class

```php
ClassBridge::session()->start($session);
// … class runs …
ClassBridge::session()->end($session);
ClassBridge::attendance()->finalize($session);
```

## 6. Join link for teacher

After creation, issue or reuse join tokens:

```php
$teacher = $session->participants()->where('role', 'teacher')->first();
$token = ClassBridge::joinTokens()->issue($teacher);

$url = $session->joinUrl($token->plainToken);
```

Share `$url` with the host client or embed it in your frontend router.
