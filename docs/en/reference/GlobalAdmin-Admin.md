# DuckPhp\GlobalAdmin\Admin

## Introduction

`Admin` is the **base class (default implementation)** of the administrator system: what is registered in `DuckPhp\DuckPhp`'s root component table is `Admin::class`, so `Admin::_()` always gives you "the administrator implementation currently in effect" — with no provider installed that is `Admin` itself (every capability method simply throws `DuckPhpSystemException`, which means "explicitly unavailable" rather than returning empty values), and once `GlobalAdmin` or your own implementation is installed it is the object registered under the `Admin::class` key.

It is at the same time the **repository of constants on the administrator side**: the event names (`EVENT_ACTION_ADMIN_*` / `EVENT_SERVICE_ADMIN_*`) and the exception codes/messages (`EXCEPTION_*`) are all defined on this class, and `GlobalAdmin` (the subclass), `Foundation\Business\BusinessHelper` and `Foundation\Controller\ControllerHelper` all reference them through the `Admin::XXX` aliases.

`GlobalAdmin` is the complete implementation of this class (session + callbacks); this class only supplies the "unavailable by default" stub implementation and the constants.

## Class info

- Namespace: `DuckPhp\GlobalAdmin`
- Declaration: `class Admin extends DuckPhp\Core\ComponentBase implements AdminActionInterface`
- Interfaces implemented: `AdminActionInterface`
- Traits used: none (`ComponentBase` already carries `SingletonExTrait`, which provides `_()`; this page does not repeat them)
- This class **declares no options at all** (`$options` is an empty array, inherited from `ComponentBase`); all options are on the subclass `GlobalAdmin`
- Constants (12 in total):

```php
const EVENT_ACTION_ADMIN_LOGINING  = 'ACTION_ADMIN_LOGINING';
const EVENT_ACTION_ADMIN_LOGINED   = 'ACTION_ADMIN_LOGINED';
const EVENT_ACTION_ADMIN_LOGOUTING = 'ACTION_ADMIN_LOGOUTING';
const EVENT_ACTION_ADMIN_LOGOUTED  = 'ACTION_ADMIN_LOGOUTED';
const EVENT_SERVICE_ADMIN_LOGINING = 'SERVICE_ADMIN_LOGINING';
const EVENT_SERVICE_ADMIN_LOGINED  = 'SERVICE_ADMIN_LOGINED';
const EVENT_SERVICE_ADMIN_LOGOUTING = 'SERVICE_ADMIN_LOGOUTING';
const EVENT_SERVICE_ADMIN_LOGOUTED = 'SERVICE_ADMIN_LOGOUTED';
const EXCEPTION_CODE_ADMIN_NEED_LOGIN          = -1;
const EXCEPTION_MESSAGE_ADMIN_NEED_LOGIN       = 'ADMIN_NEED_LOGIN';
const EXCEPTION_CODE_ADMIN_NEED_PERMISSION     = -2;
const EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION  = 'ADMIN_NEED_PERMISSION';
```

## Usage

```php
// 1) read "the current administrator" directly —— the implementation the root component table decides on
use DuckPhp\GlobalAdmin\Admin;

$id = Admin::_()->id(true);      // throws DuckPhpSystemException("No GlobalAdmin Provider.", -1) with no provider installed
$ok = Admin::_()->canAccess();   // throws DuckPhpSystemException("Need Provider", -1) with no provider installed
```

```php
// 2) swap the implementation: extend Admin (or GlobalAdmin) and mount it as ext in the application
namespace MyProject\Admin;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\GlobalAdmin\Admin;

class MyAdmin extends Admin
{
    public function init(array $options, ?object $context = null)
    {
        parent::init($options, $context);
        // register yourself under the Admin::class key, so that Admin::_() is you
        Admin::_(PhaseProxy::CreatePhaseProxy($context->getThisPhaseName(), $this));
        return $this;
    }
    public function id(bool $check_login = true)
    {
        $id = $_SESSION['admin_id'] ?? 0;
        if ($check_login && !$id) {
            throw new \DuckPhp\Core\DuckPhpSystemException('no admin login', -1);
        }
        return $id;
    }
}
// $options['ext'][MyAdmin::class] = true;
```

## Caveats

- **`Admin::_()` is a "key", not a "class"**: the object that really takes effect is the instance registered under the container key `Admin::class` (which is what `GlobalAdmin::init()` does), so callers always write `Admin::_()` and never a concrete implementation class name.
- All capability methods of this class **throw instead of returning empty values when there is no provider**: `id()`/`name()` throw `DuckPhpSystemException("No GlobalAdmin Provider.", -1 / -2)`, and `data()`/`urlFor*()`/`localService()` throw `DuckPhpSystemException("Need Provider", -1)`. This is deliberate (being unavailable by default should be loud, not silently permissive).
- The constants live on this class rather than on `GlobalAdmin` so that "the event names/exception codes can be referenced even with no provider installed" (for example when a project only wants `Admin::EVENT_ACTION_ADMIN_LOGINED` for a listener without loading the whole `GlobalAdmin`).
- The administrator side has **no registration interface**: registration (registering administrators) is not part of the back-office system, so this class has no `urlForRegister()` / `register()`; those are capabilities of the user-side `User`.
- This class declares only the `canAccess(?string $url = null, ?string $class = null, ?string $method = null)` the interface requires (**`$url` first**), and `AdminServiceInterface::canAccess()` has the same parameter order.

## Methods

### Public methods

    public function id(bool $check_login = true)
Gets the current administrator ID; this class always throws `DuckPhpSystemException("No GlobalAdmin Provider.", -1)`.

    public function name(bool $check_login = true): string
Gets the current administrator name; this class always throws `DuckPhpSystemException("No GlobalAdmin Provider.", -2)`.

    public function data(bool $check_login = true): array
Gets the current administrator's data array; this class always throws `DuckPhpSystemException("Need Provider", -1)`.

    public function urlForHome(): string
The back-office home URL; this class always throws `DuckPhpSystemException("Need Provider", -1)`.

    public function urlForLogin(?string $url_back = null): string
The back-office login URL (`$url_back` is where to return after logging in); this class always throws `DuckPhpSystemException("Need Provider", -1)`.

    public function urlForLogout(): string
The back-office logout URL; this class always throws `DuckPhpSystemException("Need Provider", -1)`.

    public function mergeViewData(array $data): array
Fills the login information into the view data: `__logined_id` (`id(true)`), `__logined_name` (`name(false)`), `__logined_data` (`data(false)`), `__logined_url_home`, `__logined_url_logout`; the implementation of this class does exactly those five things, and the header/footer are appended by the subclass `GlobalAdmin`.

    public function service()
Gets the administrator service callable across Phases: `PhaseProxy::CreatePhaseProxy(current phase, localService())`.

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
Tells whether the current administrator may access it: delegates to `localService()->canAccess($this->id(), $url, $class, $method)`.

    public function log(string $string, ?string $type = null, array $ext = [])
Records one administrator operation log entry: delegates to `localService()->log($this->id(), $string, $type, $ext)`.

    public function isSuper(): bool
Whether the current administrator is a super administrator: delegates to `localService()->isSuper($this->id())`.

### Protected methods

    protected function localService()
Returns the local (current Phase) `AdminServiceInterface` implementation; this class always throws `DuckPhpSystemException("Need Provider", -1)`.

## Related links

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — the complete implementation of this class (session + callbacks)
- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — the interface this class implements
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) — the service contract `localService()` returns
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — the cross-Phase proxy used by `service()` and for registering `Admin::_()`
- [DuckPhp\GlobalUser\User](GlobalUser-User.md) — the isomorphic base class on the user side
