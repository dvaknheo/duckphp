# DuckPhp\GlobalUser\User

## Introduction

`User` is the **base class (default implementation)** of the user system: what is registered in `DuckPhp\DuckPhp`'s root component table is `User::class`, so `User::_()` always gives you "the user implementation currently in effect" — with no provider installed that is `User` itself (every capability method simply throws `DuckPhpSystemException`, which means "explicitly unavailable" rather than returning empty values), and once `GlobalUser` or your own implementation is installed it is the object registered under the `User::class` key.

It is at the same time the **repository of constants on the user side**: the event names (`EVENT_ACTION_USER_*` / `EVENT_SERVICE_USER_*`) and the exception codes/messages (`EXCEPTION_*`) are all defined on this class, and `GlobalUser` (the subclass), `Foundation\Business\BusinessHelper` and `Foundation\Controller\ControllerHelper` all reference them through the `User::XXX` aliases.

`GlobalUser` is the complete implementation of this class (session + callbacks); this class only supplies the "unavailable by default" stub implementation and the constants.

## Class info

- Namespace: `DuckPhp\GlobalUser`
- Declaration: `class User extends DuckPhp\Core\ComponentBase implements UserActionInterface`
- Interfaces implemented: `UserActionInterface` (the login action interface `UserLoginActionInterface` is implemented by the subclass `GlobalUser`)
- Traits used: none (`ComponentBase` already carries `SingletonExTrait`, which provides `_()`; this page does not repeat them)
- This class **declares no options at all** (`$options` is an empty array, inherited from `ComponentBase`); all options are on the subclass `GlobalUser`
- Constants (16 in total):

```php
const EVENT_ACTION_USER_REGISTERING  = 'ACTION_USER_REGISTERING';
const EVENT_ACTION_USER_REGISTERED   = 'ACTION_USER_REGISTERED';
const EVENT_ACTION_USER_LOGINING     = 'ACTION_USER_LOGINING';
const EVENT_ACTION_USER_LOGINED      = 'ACTION_USER_LOGINED';
const EVENT_ACTION_USER_LOGOUTING    = 'ACTION_USER_LOGOUTING';
const EVENT_ACTION_USER_LOGOUTED     = 'ACTION_USER_LOGOUTED';
const EVENT_SERVICE_USER_REGISTERING = 'SERVICE_USER_REGISTERING';
const EVENT_SERVICE_USER_REGISTERED  = 'SERVICE_USER_REGISTERED';
const EVENT_SERVICE_USER_LOGINING    = 'SERVICE_USER_LOGINING';
const EVENT_SERVICE_USER_LOGINED     = 'SERVICE_USER_LOGINED';
const EVENT_SERVICE_USER_LOGOUTING   = 'SERVICE_USER_LOGOUTING';
const EVENT_SERVICE_USER_LOGOUTED    = 'SERVICE_USER_LOGOUTED';
const EXCEPTION_CODE_USER_NEED_LOGIN         = -1;
const EXCEPTION_MESSAGE_USER_NEED_LOGIN      = 'USER_NEED_LOGIN';
const EXCEPTION_CODE_USER_NEED_PERMISSION    = -2;
const EXCEPTION_MESSAGE_USER_NEED_PERMISSION = 'USER_NEED_PERMISSION';
```

## Usage

```php
// 1) read "the current user" directly —— the implementation the root component table decides on
use DuckPhp\GlobalUser\User;

$id = User::_()->id(true);      // throws DuckPhpSystemException("No GlobalUser Provider.", -1) with no provider installed
$ok = User::_()->canAccess();   // throws DuckPhpSystemException("Need Provider", -1) with no provider installed
```

```php
// 2) swap the implementation: extend User (or GlobalUser) and mount it as ext in the application
namespace MyProject\User;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\GlobalUser\User;

class MyUser extends User
{
    public function init(array $options, ?object $context = null)
    {
        parent::init($options, $context);
        // register yourself under the User::class key, so that User::_() is you
        User::_(PhaseProxy::CreatePhaseProxy($context->getThisPhaseName(), $this));
        return $this;
    }
    public function id(bool $check_login = true)
    {
        $id = $_SESSION['user_id'] ?? 0;
        if ($check_login && !$id) {
            throw new \DuckPhp\Core\DuckPhpSystemException('no user login', -1);
        }
        return $id;
    }
}
// $options['ext'][MyUser::class] = true;
```

## Caveats

- **`User::_()` is a "key", not a "class"**: the object that really takes effect is the instance registered under the container key `User::class` (which is what `GlobalUser::init()` does), so callers always write `User::_()` and never a concrete implementation class name.
- All capability methods of this class **throw instead of returning empty values when there is no provider**: `id()`/`name()` throw `DuckPhpSystemException("No GlobalUser Provider.", -1 / -2)`, and `data()`/`urlFor*()`/`localService()` throw `DuckPhpSystemException("Need Provider", -1)`. This is deliberate (being unavailable by default should be loud, not silently permissive).
- The constants live on this class rather than on `GlobalUser` so that "the event names/exception codes can be referenced even with no provider installed".
- The user side has two things the administrator side does not: registration-related items (`urlForRegister()`, `register()` — the latter on the subclass `GlobalUser`) and batch-fetching user names (`batchGetUsernames()`); conversely it has **no** `isSuper()`.
- `User::mergeViewData()` fills only the five `__logined_*` fields and does not touch the header/footer — the header/footer is the job of `GlobalUser::mergeViewData()`.
- This class declares only the `canAccess(?string $url = null, ?string $class = null, ?string $method = null)` the interface requires (**`$url` first**), and `UserServiceInterface::canAccess()` has the same parameter order.

## Methods

### Public methods

    public function id(bool $check_login = true)
Gets the current user ID; this class always throws `DuckPhpSystemException("No GlobalUser Provider.", -1)`.

    public function name(bool $check_login = true): string
Gets the current user name; this class always throws `DuckPhpSystemException("No GlobalUser Provider.", -2)`.

    public function data(bool $check_login = true): array
Gets the current user's data array; this class always throws `DuckPhpSystemException("Need Provider", -1)`.

    public function urlForHome(?string $url_back = null, ?array $ext = null): string
The in-site home URL; this class always throws `DuckPhpSystemException("Need Provider", -1)`.

    public function urlForRegister(?string $url_back = null, ?array $ext = null): string
The registration page URL; this class always throws `DuckPhpSystemException("Need Provider", -1)`.

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
The login page URL; this class always throws `DuckPhpSystemException("Need Provider", -1)`.

    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
The logout URL; this class always throws `DuckPhpSystemException("Need Provider", -1)`.

    public function mergeViewData(array $data): array
Fills the login information into the view data: `__logined_id` (`id(true)`), `__logined_name` (`name(false)`), `__logined_data` (`data(false)`), `__logined_url_home`, `__logined_url_logout`; the header/footer are appended by the subclass `GlobalUser`.

    public function service()
Gets the user service callable across Phases: `PhaseProxy::CreatePhaseProxy(current phase, localService())`.

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
Tells whether the current user may access it: delegates to `localService()->canAccess($this->id(), $url, $class, $method)`.

    public function log(string $string, ?string $type = null, array $ext = [])
Records one user operation log entry: delegates to `localService()->log($this->id(), $string, $type, $ext)`.

    public function batchGetUsernames(array $ids): array
Fetches user names in batch by ID: delegates to `localService()->batchGetUsernames($ids)`.

### Protected methods

    protected function localService()
Returns the local (current Phase) `UserServiceInterface` implementation; this class always throws `DuckPhpSystemException("Need Provider", -1)`.

## Related links

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — the complete implementation of this class (session + callbacks)
- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — the interface this class implements
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) — the service contract `localService()` returns
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — the cross-Phase proxy used by `service()` and for registering `User::_()`
- [DuckPhp\GlobalAdmin\Admin](GlobalAdmin-Admin.md) — the isomorphic base class on the administrator side
