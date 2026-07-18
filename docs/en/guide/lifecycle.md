# Lifecycle and Events

## Application Lifecycle

A DuckPHP application goes through a series of stages from startup to completion, each with corresponding hook methods for developers to intervene.

### Complete Flow

```
App::RunQuickly($options)
  │
  ├─ new App() + init($options)
  │    │
  │    ├─ initOptions()          ← Merge options
  │    ├─ initContainer()        ← Initialize phase container
  │    ├─ initException()        ← Set exception/error handlers
  │    ├─ onPrepare()            ← Prepare callback ①
  │    ├─ prepareComponents()    ← Prepare components
  │    ├─ initComponents()       ← Initialize components (route, view, etc.)
  │    ├─ initExtentions(ext)    ← Initialize extensions
  │    ├─ onInit()               ← Init complete callback ②
  │    ├─ onBeforeChildrenInit() ← Before child app init callback ③
  │    ├─ initExtentions(app)    ← Initialize child apps
  │    ├─ is_inited = true
  │    └─ onInited()             ← All ready callback ④
  │
  ├─ run()
  │    │
  │    ├─ onBeforeRun()          ← Before run callback ⑤
  │    ├─ Route execution
  │    │    ├─ prepend-outter hooks (check status, rewrite)
  │    │    ├─ prepend-inner hooks (route mapping)
  │    │    ├─ Default route → controller method
  │    │    ├─ append-inner hooks
  │    │    └─ append-outter hooks (resources, route mapping)
  │    ├─ If route not handled: → _On404()
  │    └─ onAfterRun()           ← After run callback ⑥
  │
  └─ Return true/false
```

### Overriding Lifecycle Methods

Override these methods in `src/System/App.php`:

```php
namespace MyProject\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    protected function onPrepare()
    {
        parent::onPrepare();
        // 1. Preparation phase: load configurations, register extensions
        // Components are not fully initialized at this point
    }

    protected function onInit()
    {
        parent::onInit();
        // 2. Framework initialization complete, can operate components
        $this->assignRoute('home', function () {
            echo "Custom home";
        });
    }

    protected function onInited()
    {
        parent::onInited();
        // 3. All initialization complete (including child apps)
        // This is the last callback before running
    }

    protected function onBeforeRun()
    {
        parent::onBeforeRun();
        // 4. About to start route dispatching
    }

    protected function onAfterRun()
    {
        parent::onAfterRun();
        // 5. Request processing complete
    }
}
```

## Session Management

Recommended approach: Use `SessionTrait` (lazy startup)

Session belongs to the Controller layer. The division of labor with Action and MainController is as follows:

```
Controller Layer
  ├── MainController    Route entry (input/output)
  ├── Action            Common orchestration (calls Business + Session)
  └── Session          Pure state container (does not call other classes)
                              │
Business Layer                   ▼
  └── UserBusiness      Business logic (calls Model, does not touch Session)
```

`SessionTrait` automatically calls `session_start()` on the first read/write, no need to manually start:

```php
<?php
namespace MyProject\Controller;

use DuckPhp\Foundation\SessionTrait;

class Session
{
    use SessionTrait;  // Built-in lazy session_start() + get/set/unset
    
    const KEY_USER_ID = 'user_id';
    
    public function setUserId($id): void
    {
        $this->set(static::KEY_USER_ID, $id);
    }
    
    public function getUserId(): int
    {
        return (int)$this->get(static::KEY_USER_ID, 0);
    }
    
    public function clearUserId(): void
    {
        $this->unset(static::KEY_USER_ID);
    }
    
    public function isLoggedIn(): bool
    {
        return $this->getUserId() > 0;
    }
}
```

On top of Session, create Action classes to encapsulate reusable orchestration logic. Action **can only call Business and Session, cannot directly call Model**:

```php
<?php
namespace MyProject\Controller;

use MyProject\Business\UserBusiness;
use MyProject\Controller\Session;

class UserAction
{
    public function login(string $username, string $password): array
    {
        $user = UserBusiness::_()->login($username, $password);
        Session::_()->setUserId($user['id']);
        return $user;
    }
    
    public function getCurrentUser(): ?array
    {
        $id = Session::_()->getUserId();
        return $id ? UserBusiness::_()->getUser($id) : null;  // → Business, not directly calling Model
    }
    
    public function isLoggedIn(): bool
    {
        return Session::_()->isLoggedIn();
    }
    
    public function logout(): void
    {
        Session::_()->clearUserId();
    }
}
```

Controller only does input/output, delegating to Action:

```php
class MainController
{
    public function action_login()
    {
        if (UserAction::_()->isLoggedIn()) { /* redirect */ }
        try {
            UserAction::_()->login($username, $password);
        } catch (\Exception $ex) { /* show error */ }
    }
    
    public function action_profile()
    {
        $user = UserAction::_()->getCurrentUser();
        // ...
    }
    
    public function action_logout()
    {
        UserAction::_()->logout();
        Helper::Show302('');
    }
}
```

`SessionTrait` provides three protected methods:

| Method | Description |
|---|---|
| `get($key, $default)` | Read Session, auto `session_start()` |
| `set($key, $value)` | Write Session, auto `session_start()` |
| `unset($key)` | Delete Session key, auto `session_start()` |

This way the Business layer remains purely stateless and can be reused in any environment such as CLI, tests, and APIs.

### Low-Level Session Read/Write

If you need to directly read/write Session in the Controller layer (without using `SessionTrait`):

```php
use DuckPhp\Core\SuperGlobal;

// Note: You need to manually session_start() before directly using SuperGlobal
\DuckPhp\Core\SystemWrapper::_()->_session_start();

// Write Session
SuperGlobal::_()->_SessionSet('cart_items', $items);

// Read Session
$items = SuperGlobal::_()->_SessionGet('cart_items', []);

// Delete Session key
SuperGlobal::_()->_SessionUnset('cart_items');
```

## Event System

The framework has a built-in event manager `DuckPhp\Core\EventManager`, supporting custom event listening and triggering.

### Triggering Events

```php
use DuckPhp\Core\EventManager;

EventManager::FireEvent('user_registered', $userId, $username);
// Or
Helper::FireEvent('user_registered', $userId, $username);
```

### Listening to Events

```php
// Register in App::onInit() or other initialization phases
EventManager::OnEvent('user_registered', function ($userId, $username) {
    // Send welcome email
    // Log
});
```

### Removing Events

```php
EventManager::RemoveEvent('user_registered');
// Or remove specific callback
EventManager::RemoveEvent('user_registered', $callback);
```

### Framework Built-in Events

The framework itself triggers events at certain lifecycle points:

```php
App::FireEvent('onPrepare');      // Corresponds to onPrepare()
App::FireEvent('onInit');          // Corresponds to onInit()
App::FireEvent('onInited');       // Corresponds to onInited()
App::FireEvent('onBeforeRun');    // Corresponds to onBeforeRun()
App::FireEvent('onAfterRun');     // Corresponds to onAfterRun()
App::FireEvent('onBeforeOutput'); // Before output
App::FireEvent('On404');          // On 404
```

## Exception Handling

### Exception Hierarchy

```
\Exception                     # PHP built-in
  └─ {project}\ProjectException     # Project exception base class
       ├─ {project}\BusinessException    # Business layer exception
       └─ {project}\ControllerException  # Controller layer exception
```

### Configuring Exception Handling

```php
class App extends DuckPhp
{
    public $options = [
        'exception_for_project' => ProjectException::class,
        'exception_for_business' => BusinessException::class,
        'exception_for_controller' => ControllerException::class,
        'exception_reporter' => ExceptionReporter::class,
    ];
}
```

### Conditional Throwing

```php
// In Controller
Helper::ControllerThrowOn($flag, 'Permission denied', 403);

// In Business
Helper::BusinessThrowOn($flag, 'Insufficient balance', 1001);
```

### Custom Exception Reporter

```php
namespace MyProject\Controller;

class ExceptionReporter
{
    public static function OnException(\Throwable $ex)
    {
        // Log exception
        // Return error response
    }
}
```

## Debug Mode

After enabling debug mode, the framework displays detailed error information:

```php
$options = [
    'is_debug' => true,
];
```

Global functions available in debug mode:

```php
__var_dump($var);     // var_dump in page (only visible in debug mode)
__var_log($var);      // Log variable to log
__trace_dump();       // Print call stack
__debug_log('msg');   // Write debug log
__is_debug();         // Check if debug mode
```
