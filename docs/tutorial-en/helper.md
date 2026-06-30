# Helper Classes

DuckPHP provides `Helper` static helper classes at each layer to encapsulate access to framework components. Each layer's `Helper` can only access the features allowed for that layer. Once you understand the application structure, mastering these helper functions is enough for daily development.

Before learning Helper, it is recommended to read [Project Structure and Coding Rules](project-structure.md) to understand the responsibilities of each layer.

## Helper Class Layers

| Namespace | Layer | Main Responsibilities |
|---|---|---|
| `DuckPhp\Foundation\Controller\Helper` | Controller layer | Request input, view output, route parameters, HTTP responses |
| `DuckPhp\Foundation\Business\Helper` | Business layer | Business exceptions, cache, config reading, event triggering |
| `DuckPhp\Foundation\Model\Helper` | Model layer | Database connections, SQL pagination |
| `DuckPhp\Foundation\System\Helper` | System layer | Route hooks, Session, Redis, CLI parameters, system functions |

> **Note**: Helper classes at different layers have different functions, do not confuse them. For example, the Controller layer should use `DuckPhp\Foundation\Controller\Helper`, while the Model layer should use `DuckPhp\Foundation\Model\Helper`.

> **Naming Convention**: Methods starting with uppercase are commonly used; methods starting with lowercase are less commonly used. The Controller helper class also has some all-lowercase methods with the same names as PHP global functions (such as `header()`, `setcookie()`, `exit()`), used as replacements for those global functions to ensure compatibility.

## Controller Helper

The Controller layer's `Helper` handles HTTP requests, view rendering, and responses.

### Request Data

```php
use DuckPhp\Foundation\Controller\Helper;

$id = Helper::GET('id', 0);              // Get $_GET['id'], default 0
$name = Helper::POST('name', '');        // Get $_POST['name'], default empty string
$page = Helper::REQUEST('page', 1);      // Get $_REQUEST['page']
$host = Helper::SERVER('HTTP_HOST');     // Get $_SERVER['HTTP_HOST']
$token = Helper::COOKIE('token');        // Get $_COOKIE['token']
```

> It is recommended to use these helper methods instead of PHP native superglobal variables, for compatibility with different runtime environments (such as Swoole, WorkerMan, etc.).

### View Rendering

```php
// Render view (automatically includes header and footer)
Helper::Show(get_defined_vars(), 'user/profile');

// Render view fragment (without header and footer)
Helper::Display('user/profile', $data);

// Render as string
$html = Helper::Render('user/profile', $data);

// Set header and footer
Helper::setViewHeadFoot('_sys/header', '_sys/footer');

// Assign view variable
Helper::assignViewData('site_name', 'MySite');
```

- `Show()` is used for controller output. When the second parameter is `null`, it automatically finds the view file corresponding to `{Controller}/{Method}`.
- `Render()` is often used for special output processing of a certain block.
- `assignViewData()` is generally used in base classes to provide common data for headers and footers.

### URL and Routing

```php
$url = Helper::Url('user/login');        // Generate URL
$res = Helper::Res('css/style.css');     // Generate resource URL
$domain = Helper::Domain(true);          // Get current domain
$pathInfo = Helper::PathInfo();          // Get PATH_INFO
$param = Helper::Parameter('id');        // Get route parameter
```

### HTTP Response

```php
Helper::Show302('user/login');           // 302 redirect
Helper::Show404();                       // Show 404
Helper::ShowJson(['code' => 0]);        // Output JSON
Helper::header('Content-Type: application/json');
Helper::setcookie('name', 'value', 3600);
Helper::exit();
```

> All-lowercase methods such as `header()`, `setcookie()`, and `exit()` are used as replacements for PHP global functions with the same names, ensuring compatibility in different runtime environments.

### Exceptions and Events

```php
// Conditionally throw Controller exception
Helper::ControllerThrowOn(!$user, 'Please log in first', 403);

// Register exception handler
Helper::assignExceptionHandler(MyException::class, function ($ex) {
    // Handle exception
});

// Trigger event
Helper::FireEvent('user_login', $userId);
Helper::OnEvent('user_login', function ($userId) {
    // Listen to event
});
```

### Pagination

```php
$total = UserModel::_()->getTotal();
$html = Helper::PageHtml($total, ['page_size' => 20]);
$pageNo = Helper::PageNo();
```

### Global User/Admin

```php
$userId = Helper::UserId();              // Current user ID
$user = Helper::User();                  // Current user object
$userName = Helper::UserName();          // Current username
$userService = Helper::UserService();    // User service

$adminId = Helper::AdminId();            // Current admin ID
$admin = Helper::Admin();                // Current admin object
$adminService = Helper::AdminService();  // Admin service
```

> Global user/admin is an advanced topic, usually used when integrating third-party management systems or the `GlobalUser`/`GlobalAdmin` extensions.

## Business Helper

The Business layer's `Helper` provides stateless business auxiliary functions.

### Configuration and Exceptions

```php
use DuckPhp\Foundation\Business\Helper;

$dbConfig = Helper::Setting('database_list');
$appConfig = Helper::Config('app', 'key', 'default');

// Conditionally throw Business exception
Helper::BusinessThrowOn($balance < $amount, 'Insufficient balance', 1001);
```

### Cache and Events

```php
$cache = Helper::Cache();
$cache->set('key', 'value', 3600);
$value = $cache->get('key', 'default');

Helper::FireEvent('order_created', $orderId);
```

### Paths

```php
$projectPath = Helper::PathOfProject();  // Project path
$runtimePath = Helper::PathOfRuntime();  // Writable runtime path
```

### Safe Call

```php
$result = Helper::XpCall(function () {
    return SomeService::_()->riskyOperation();
});
```

## Model Helper

The Model layer's `Helper` only provides database access related functions.

```php
use DuckPhp\Foundation\Model\Helper;

// Get database connection
$db = Helper::Db(0);                     // Specify tag
$dbRead = Helper::DbForRead();           // Read connection
$dbWrite = Helper::DbForWrite();         // Write connection

// Query
$rows = Helper::DbForRead()->fetchAll("SELECT * FROM users WHERE status=?", 1);
Helper::DbForWrite()->execute("UPDATE users SET name=? WHERE id=?", 'foo', 1);

// Pagination SQL
$sql = Helper::SqlForPager("SELECT * FROM users", $page, 10);
$countSql = Helper::SqlForCountSimply("SELECT * FROM users");
```

## System Helper

The System layer's `Helper` provides framework-level system functions, usually used in `App` or system configuration. The following content is advanced; detailed explanations can be found in source code comments and reference documentation.

### Route Hooks

```php
use DuckPhp\Foundation\System\Helper;

Helper::addRouteHook(function ($path_info) {
    if ($path_info === '/special') {
        echo 'Special route';
        return true;
    }
    return false;
}, 'prepend-inner');

Helper::assignRoute('/hello', function () {
    echo 'Hello';
});

Helper::assignRewrite('article/123', 'blog/show?id=123');
```

### Session Operations

```php
Helper::session_start();
Helper::SessionSet('user_id', 123);
$userId = Helper::SessionGet('user_id', 0);
Helper::SessionUnset('user_id');
```

### Database and Redis

```php
Helper::DbCloseAll();                    // Close all database connections
$redis = Helper::Redis(0);               // Get Redis connection
```

### System Function Wrappers

```php
Helper::header('Content-Type: text/html');
Helper::setcookie('name', 'value', 3600);
Helper::exit(0);
Helper::register_shutdown_function(function () {
    // Cleanup work
});
```

### CLI Parameters

```php
$params = Helper::getCliParameters();
```

## Helper Usage Principles

1. **Use by layer**: Controller uses `Controller\Helper`, Business uses `Business\Helper`, Model uses `Model\Helper`
2. **No cross-layer calls**: Business layer should not call `Controller\Helper`, Model layer should not call `Business\Helper`
3. **Stay stateless**: Helper operations in Business and Model layers should not depend on request context
4. **Prefer Foundation Helper**: Avoid directly calling classes under the `DuckPhp` namespace in Controller/Business/Model

## Helper vs Global Functions

Many global functions are actually proxied through `CoreHelper`, accessing the same set of components as Helper classes:

```php
// The following two are equivalent
Helper::Url('user/login');
__url('user/login');

// The following two are equivalent
Helper::ShowJson($data);
__json($data);
```

Global functions are more suitable for use in views, while Helper classes are more suitable for use in controllers and business classes.
