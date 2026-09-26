# 2-19 Using the user system

> What this solves: answering "who is logged in", "may they do this" and "whose ids are these" inside your controllers and business code.
> Prerequisites: [Chapter 2-5 Controllers](controllers.md), [Chapter 2-11 Sessions](session.md), [Chapter 2-12 Exceptions and error handling](exception.md). About 15 minutes.
> This chapter covers **usage only**; for "how the user system gets wired in" (three implementations + options + the `ext` mount) see [Chapter 4-11 Implementing the user system](impl-user.md). **Before it is wired**, these entry points throw `DuckPhpSystemException` outright (they do not silently return 0).
> Runnable assets: `tests/Foundation/Controller/UserControllerBaseTest.php`, `tests/GlobalUser/GlobalUserTest.php`.

## Minimal example

Inside a controller you ask three things:

```php
// MyProj\Controller\UserController
public function center()
{
    $userId = Helper::UserId();          // the current user id; not logged in -> 302 to the login page and the request ends
    $name   = Helper::UserName();        // the current user name
    $url    = Helper::User()->urlForLogin();   // when you need the login page address

    Helper::Show(get_defined_vars(), 'user/center');
}
```

The business layer cannot see "who is logged in", but it can reach the **user service**:

```php
// MyProj\Business\NoteService
public function listWithAuthors(int $userId): array
{
    $notes = NoteModel::_()->listByUser($userId);
    $names = Helper::UserService()->batchGetUsernames(array_column($notes, 'user_id'));

    foreach ($notes as &$note) {
        $note['author'] = $names[$note['user_id']] ?? '';
    }
    return $notes;
}
```

> The two `Helper::` classes here are **different layers**: in the controller it is `MyProj\Controller\Helper` (extending [`ControllerHelper`](../reference/Foundation-Controller-ControllerHelper.md)), and in business code `MyProj\Business\Helper` (extending [`BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md)). The controller layer has "a person"; the business layer only has "a service" — see the next section.

## How it works

### 1. Two entry sets: the controller gets "the person", business gets "the service"

| Which layer you are in | Entry point | What you get | What you can do |
|---|---|---|---|
| Controller | `Helper::User()` | [`UserActionInterface`](../reference/GlobalUser-UserActionInterface.md) (the current user object) | see the table in §2 |
| Controller | `Helper::UserId()` / `Helper::UserName()` | `int\|string` / `string` | shorthands when you only want the id or the name |
| Controller | `Helper::UserService()` | [`UserServiceInterface`](../reference/GlobalUser-UserServiceInterface.md) | `canAccess()` / `log()` / `batchGetUsernames()` |
| Business | `Helper::UserService()` | The same thing — **the same service** | the same |
| Anywhere | [`User::_()`](../reference/GlobalUser-User.md) | The current user object (`Helper::User()` is exactly this) | the same, one forwarding layer less |

Why the business layer has no `User()` / `UserId()`: **"who is logged in" is request context** (session plus current route), which is the controller's business. A business method that needs user information takes it **explicitly** (`function listWithAuthors(int $userId)`), which is what lets the same business code be reused by CLI, scheduled tasks and queues — that is exactly the layering rule in [Chapter 2-1](layers.md).

### 2. What `Helper::User()` can do

| Method | Returns | Notes |
|---|---|---|
| `id(bool $check_login = true)` | `int\|string` | The current user id; for "not logged in" see §3 |
| `name(bool $check_login = true)` | `string` | The current user name |
| `data(bool $check_login = true)` | `array` | The whole current-user record (whatever is in the session) |
| `canAccess(?string $url = null, ?string $class = null, ?string $method = null)` | `bool` | The permission test; **with no arguments it uses the current route's** class/method/URL; not logged in is simply `false` |
| `log(string $string, ?string $type = null, array $ext = [])` | — | Records one user action log, landing in your `UserServiceInterface::log()` |
| `urlForHome()` / `urlForLogin($url_back = null)` / `urlForLogout()` / `urlForRegister()` | `string` | Site URLs; `urlForLogin('/order/1')` carries a `?b=` return parameter |
| `service()` | `UserServiceInterface` | Equivalent to `Helper::UserService()` |
| `batchGetUsernames(array $ids)` | `array` | Equivalent to `Helper::UserService()->batchGetUsernames()` (`[id => name]`) |

> Full signatures and contracts for each method are in the [reference manual](../reference/GlobalUser-UserActionInterface.md); this is only the caller's-eye summary.

### 3. What happens when nobody is logged in

`id()` / `name()` / `data()` default to `$check_login = true`, and then **being logged out does not return 0**: one of three things happens per your configuration, and **all three paths end the request** (`exit()`):

| Your configuration | Behaviour |
|---|---|
| `globaluser_need_login_callback` set | your callback runs (e.g. output a 401 JSON), then the request ends |
| not set, and not Ajax | `302` to `urlForLogin(the path of the current REQUEST_URI)`; the browser goes back to the login page |
| not set, and Ajax | outputs `{"error_code":-1,"error_message":"NEED_LOGIN"}` |

So "the caller gets no return value" is **deliberate**: the default behaviour is "not logged in → go to the login page", and you write nothing. To let the code continue and decide yourself, pass `false`:

```php
$userId = Helper::UserId(false);          // 0 when not logged in; no redirect, no exit
if (!$userId) {
    Helper::Show302(Helper::User()->urlForLogin('user/center'));   // supply the return address yourself
    return;
}
```

What `data()` returns depends on your session implementation; with the bundled [`UserSessionTrait`](../reference/GlobalUser-UserSessionTrait.md) it is `['id' => …, 'name' => …]`.

### 4. `Helper::UserService()`: usable from controllers and business alike

The service is pure logic taking `$user_id` explicitly and never reading the session, so both layers can call it safely (internally it is wrapped in [`PhaseProxy`](../reference/Component-PhaseProxy.md), so it also works across child-app phases):

```php
Helper::UserService()->canAccess($userId, $url, $class, $method);   // argument order: $url first
Helper::UserService()->log($userId, 'exported a report', 'export', ['rows' => 120]);
Helper::UserService()->batchGetUsernames([3, 5, 8]);                // [3 => 'Alice', …]
```

Inside a controller, if you want "the current user against the current request", the argument-less `Helper::User()->canAccess()` is easier (it fills in the current route's class/method/URL and the current user id for you).

### 5. The three actions: login / register / logout

```php
public function login()
{
    if (Helper::POST()) {
        Helper::User()->login(Helper::POST());     // validation and persistence belong to the login service; on success it 302s to globaluser_url_home
    }
    Helper::Show([], 'user/login');
}
public function register()
{
    if (Helper::POST()) {
        Helper::User()->register(Helper::POST());  // likewise; on success it also 302s to globaluser_url_home
    }
    Helper::Show([], 'user/register');
}
public function logout()
{
    Helper::User()->logout();                      // clears the session and 302s to globaluser_url_login
}
```

All three actions **redirect with 302 automatically** (controlled by `globaluser_is_authed_redirect`, on by default); turn it off and you decide where to go. To observe "login succeeded", use events rather than editing these methods: the `EVENT_ACTION_USER_LOGINED` group ([Chapter 2-13](events.md)).

### 6. The user page's header/footer: two view switches

User pages (the user centre, a profile) usually carry the user header/footer and read `__logined_id` / `__logined_name` / `__logined_url_home` / `__logined_url_logout` / `__logined_data` in the view. The switches are **view data**:

| View-data key | Effect |
|---|---|
| `__use_logined_view_data` | only when true are those `__logined_*` values injected (otherwise it is ordinary rendering) |
| `__use_logined_header_footer_file` | only when true is the user page's header/footer template wrapped around it |
| `__logined_render_header_footer` | treated as true when absent; set it to `false` for "inject the data but do not wrap the templates" |

**Extending [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md) sets the first two to true automatically**, so you write nothing; if your own base class wants them, copy this:

```php
Helper::assignViewData('__use_logined_view_data', true);
Helper::assignViewData('__use_logined_header_footer_file', true);
```

Both the test and the rendering live in [`DuckPhp::_Show()`](../reference/DuckPhp.md): it only takes over when the current route's controller implements [`UserControllerInterface`](../reference/GlobalUser-UserControllerInterface.md), and the header/footer templates come from `globaluser_view_file_header/footer` (**phase-overridable**; a third app can replace them, see [Chapter 3-5](overriding.md)).

> How to configure those templates themselves belongs to the "implementation" side: [Chapter 4-11 §7](impl-user.md).

## Common patterns

**① A page that requires a login (you handle the redirect)**

```php
public function profile()
{
    $userId = Helper::UserId(false);
    if (!$userId) {
        Helper::Show302(Helper::User()->urlForLogin('user/profile'));
        return;
    }
    Helper::Show(get_defined_vars(), 'user/profile');
}
```

**② No login required, but show the name when logged in**

```php
$name = Helper::UserName(false);     // an empty string when logged out; no redirect
Helper::Show(['name' => $name], 'home');
```

**③ Checking a permission (for the current request)**

```php
if (!Helper::User()->canAccess()) {      // no arguments: current route + current user
    Helper::Show302(Helper::Url('/'));   // or leave it to UserControllerBase's onNeedPermission()
    return;
}
```

**④ Using the login information in a view**

```php
<?php if (!empty($__logined_id)): ?>
    Hello, <?= __h($__logined_name) ?> · <a href="<?= __h($__logined_url_logout) ?>">log out</a>
<?php endif; ?>
```

**⑤ Fetching names in bulk (business layer)**

```php
$names = Helper::UserService()->batchGetUsernames($userIds);   // [id => name]; one query, never query inside a loop
```

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| Calling `Helper::UserId()` sends the page straight into a 302 | `$check_login` defaults to `true`, so "not logged in" redirects to the login page and ends the request | Pass `false` if you want to decide yourself (§3) |
| Writing `Helper::UserId()` in the business layer reports a missing method | The business-layer Helper has no such method (only `UserService()`) | Pass `$userId` into the business method from the controller |
| `DuckPhpSystemException: No GlobalUser Provider.` | The user system is not wired yet (`ext` is not mounted) | See [Chapter 4-11](impl-user.md) |
| `Helper::User()->canAccess()` always returns `false` | Nobody is logged in, or your `UserServiceInterface::canAccess()` rules say so | Check `Helper::UserId(false)` first, then inspect the service implementation |
| The page has no user header/footer | The controller does not implement `UserControllerInterface`, or the two view-data keys are not true | Extend `UserControllerBase`, or call `assignViewData` yourself (§6) |
| `$__logined_name` is undefined in the view | `__use_logined_view_data` is off (so ordinary rendering was used) | Turn that key on, or use `Helper::UserName(false)` |

## Next steps

- [Chapter 2-20 Using the admin system](admin.md): the back-office entry points, parallel to this chapter.
- [Chapter 4-11 Implementing the user system](impl-user.md): who provides this project's user system, and how the options are configured.
- [Chapter 2-13 The event system](events.md): how to listen for `EVENT_ACTION_USER_*` (around register/login/logout).
- [Chapter 2-12 Exceptions and error handling](exception.md): how an expired login or missing permission becomes a redirect or an error page.
- Reference manual: [User](../reference/GlobalUser-User.md), [UserActionInterface](../reference/GlobalUser-UserActionInterface.md), [GlobalUser](../reference/GlobalUser-GlobalUser.md), [UserLoginActionInterface](../reference/GlobalUser-UserLoginActionInterface.md), [UserServiceInterface](../reference/GlobalUser-UserServiceInterface.md), [UserSessionTrait](../reference/GlobalUser-UserSessionTrait.md)
