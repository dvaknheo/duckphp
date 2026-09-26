# 2-11 Session

> What this solves: how to read and write the session, how prefixes isolate it, what belongs in a session, and the division of labor between "session" and "login state".
> Prerequisites: [Chapter 2-5 Controllers](controllers.md), [Chapter 1-5 Configuration and Settings](configuration.md). About 10 minutes.
> Example: `demo/src/Controller/Session.php` (the smallest session class in the framework skeleton).

## Minimal example

Session capability is one trait + one thin shell class. Note: **the trait gives you `protected` `get()`/`set()`/`unset()`, so wrap them in public semantic methods inside the session class** — key names live inside the class, call sites only see method names:

```php
<?php declare(strict_types=1);
namespace ProjectNameTemplate\Controller;

use DuckPhp\Foundation\Controller\SessionTrait;

class Session
{
    use SessionTrait;

    public function setLastNote(int $id): void { $this->set('last_note', $id); }
    public function getLastNote(): ?int        { return $this->get('last_note') ?: null; }
}
```

Controllers only call those public methods:

```php
public function remember()
{
    Session::_()->setLastNote((int)Helper::POST('id'));
    Helper::Show(['last' => Session::_()->getLastNote()], 'note/index');
}
```

> ⚠️ Don't write `Session::_()->set('k', $v)` directly in a controller: the trait's methods are `protected` and you will get
> `Error: Call to protected method`. The sample methods in the skeleton `skeleton/src/Controller/Session.php` (= `demo/src/Controller/Session.php`)
> are commented out by default — add your own.

## How it works

### 1. SessionTrait: prefixed reads and writes

[DuckPhp\Foundation\Controller\SessionTrait](../reference/Foundation-Controller-SessionTrait.md) (source `src/Foundation/Controller/SessionTrait.php`) adds three `protected` methods to any class:

| Method | Purpose |
|---|---|
| `get(string $key, $default = null)` | Read `session_prefix . $key` |
| `set(string $key, $value)` | Write `session_prefix . $key` |
| `unset(string $key)` | Delete `session_prefix . $key` |
| `checkSessionStart(): void` | Ensure the session has started and cache `session_prefix` (idempotent, internal method) |

- `session_start()` happens automatically on first read/write (called through [`SystemWrapper`](../reference/Core-SystemWrapper.md), which you can replace in tests — see [Chapter 2-17](testing.md));
- Underneath it goes through [DuckPhp\Core\SuperGlobal](../reference/Core-SuperGlobal.md)'s `_SessionGet/_SessionSet/_SessionUnset`;
- All three methods are `protected`, **for your own class only**, not made into a static API — session reads and writes should live in the project's `Controller\Session`-type class.

### 2. `session_prefix`: isolation for multiple apps in one process

`session_prefix` is a hidden option (default empty string; see [the settings page of the reference manual](../reference/setting.md)). When several apps are mounted in the same PHP process ([Chapter 4-6](multi-entry.md)), give each app a different prefix so session keys don't overwrite each other:

```php
// in the child app's App
public $options = [
    'session_prefix' => 'shop_',      // so $this->set('uid', 1) in the session class actually writes shop_uid
];
```

### 3. What goes in a session: "tickets" only, not "data"

A session is only suitable for **identifiers and a little state** (current user id, the previous URL, one-shot notices); everything else is looked up in the database by id:

| What | Example | Why |
|---|---|---|
| ✅ Identifiers | `$this->set('uid', $id)` in the session class | The session is a client credential — the less the better |
| ✅ One-shot notices | flash messages, `url_back` | Used on the next request, then discarded |
| ❌ Business data snapshots | the whole order array | Goes stale, bloats, drifts out of sync with the database |

### 4. Session ≠ login state

`SessionTrait` only solves "how to read and write the session". **Who is current, how login works, what to do when not logged in** is another set of things:

- User system: [Chapter 2-19 Using the User System](user.md) (the caller's view), [Chapter 4-11 Implementing the User System](impl-user.md) (`UserSessionTrait` stores the current user in the session key `user`);
- Admin system: [Chapter 2-20 Using the Admin System](admin.md), [Chapter 4-12 Implementing the Admin System](impl-admin.md) (`AdminSessionTrait`, key `admin`).

Internally they are implemented by **composing `SessionTrait`** — which is also why this chapter is a prerequisite of those two.

## Common patterns

**① Wrap semantic methods inside the session class** (recommended: call sites never see key names)

```php
class Session
{
    use SessionTrait;

    public function setCurrentUserId(int $id): void   { $this->set('uid', $id); }
    public function getCurrentUserId(): ?int          { return $this->get('uid') ?: null; }
    public function forgetCurrentUser(): void         { $this->unset('uid'); }
}
```

**② One-shot notice (flash): write + clear on read**

```php
// in the session class: a pair of methods; unset on read
public function flash(string $msg): void { $this->set('flash', $msg); }
public function takeFlash(): ?string
{
    $msg = (string)$this->get('flash');
    $this->unset('flash');
    return $msg ?: null;
}

// in the controller
Session::_()->flash('保存成功');     // write
$msg = Session::_()->takeFlash(); // read and clear immediately, avoiding duplicate display
```

**③ Swap out the session implementation (tests / long-running processes)**

```php
Helper::system_wrapper_replace([
    'session_start' => function ($options = []) { /* 假装启动，比如挂到数组上 */ },
]);
```

**④ Use the session for "back to the original page after login"**

```php
// in the session class
public function setUrlBack(string $url): void { $this->set('url_back', $url); }
public function takeUrlBack(string $fallback = 'home/index'): string
{
    return (string)$this->get('url_back', $fallback);
}

// before the not-logged-in redirect
Session::_()->setUrlBack(Helper::PathInfo());
// after successful login
Helper::Show302(Session::_()->takeUrlBack());
```

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| Wrote a value but can't read it | The two requests have different `session_prefix` (multiple apps / subdirectories) | Different prefixes are different keys; align the prefix or use the same app |
| `session_start()` reports "headers already sent" | Output preceded session start (side effects of `echo` in views) | Make the first session read/write happen before output; or have the class that uses `SessionTrait` probe once in its constructor |
| Session state bleeds between tests | The session is process/global state | Replace `session_*` with `system_wrapper_replace`, or swap `Session::_()` for a local implementation |
| Calling `Session::_()->set('k', $v)` throws `Call to protected method` | `SessionTrait`'s three methods are `protected`, callable only inside the session class | Wrap public semantic methods in the session class; call sites use only those (see "Common patterns" ①②④) |
| Slow after stuffing big arrays into the session | The session is read/written in full every request and serialized | Store only identifiers; put data in the database/cache |

## Next steps

- [Chapter 2-19 Using the User System](user.md): how that little "login ticket" in the session becomes "the current user".
- [Chapter 2-20 Using the Admin System](admin.md): backend login, permission checks, and menus.
- [Chapter 2-2 The Request Lifecycle](lifecycle.md): what the framework does inside a request.
- The reference manual: [Foundation\Controller\SessionTrait](../reference/Foundation-Controller-SessionTrait.md), [Core\SuperGlobal](../reference/Core-SuperGlobal.md), [Core\SystemWrapper](../reference/Core-SystemWrapper.md), [App Settings](../reference/setting.md)
