# Four-Layer Architecture (Controller → Business → Model → View)

DuckPHP adopts a **Controller → Business → Model → View** four-layer architecture.

### Complete Layer Overview

```
Controller Layer (can handle request context)
  ├── MainController      Route entry, input/output (calls Business)
  ├── Helper              Static helper methods
  ├── Session             Pure state container (does not call other classes)
  └── Action              Functionality reuse
                                │
Business Layer (purely stateless) ▼
  ├── UserBusiness         Business logic (calls Model)
  ├── Service              Common functionality (calls Model)
  └── Helper               Static helper methods
                                │
Model Layer (purely stateless)   ▼
  └── UserModel            Data access (single-table CRUD)
                                │
View Layer                       ▼
  └── *.php                Template rendering
```

> **Coding Rule**: Except for basic code, the `Controller`, `Business`, and `Model` layers should not directly call classes under the `DuckPhp` namespace. Framework-related calls are centralized in the `System` layer.

## Controller

The controller is the entry point for HTTP requests, responsible for:
- Receiving user input (GET/POST, etc.)
- Calling the Business layer to process business logic
- Deciding output (view/JSON/redirect, etc.)

### Basic Usage

```php
<?php
namespace MyProject\Controller;

use MyProject\Business\MyBusiness;
use DuckPhp\Foundation\Controller\Helper;

class MyController
{
    public function action_index()
    {
        // Get business data
        $data = MyBusiness::_()->getList();
        
        // Render view
        Helper::Show(get_defined_vars(), 'my/index');
    }
    
    public function action_detail()
    {
        $id = Helper::GET('id');
        $item = MyBusiness::_()->getDetail($id);
        
        Helper::Show(get_defined_vars());
        // View file defaults to ControllerClass/actionMethod, i.e., MyController/action_detail
    }
}
```

### Using Foundation Base Class (Recommended)

```php
<?php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\Base;

class MainController extends Base
{
    public function action_index()
    {
        Helper::Show(get_defined_vars(), 'main');
    }
}
```

After inheriting from `Foundation\Controller\Base`, you automatically get:
- `_()` variable singleton call
- Controller URL auto-replacement functionality
- Class name checking (`controller_class_postfix` + `controller_class_base`)

### Helper Methods Quick Reference

| Method | Description |
|---|---|
| `Helper::Show($data, $view)` | Render view |
| `Helper::Display($view, $data)` | Display view fragment directly |
| `Helper::Render($view, $data)` | Render as string |
| `Helper::GET($key)` | Get `$_GET` |
| `Helper::POST($key)` | Get `$_POST` |
| `Helper::REQUEST($key)` | Get `$_REQUEST` |
| `Helper::SERVER($key)` | Get `$_SERVER` |
| `Helper::COOKIE($key)` | Get `$_COOKIE` |
| `Helper::Url($url)` | Generate URL |
| `Helper::Res($url)` | Generate resource URL |
| `Helper::ShowJson($data)` | Output JSON |
| `Helper::Show302($url)` | Redirect |
| `Helper::Show404()` | Show 404 |
| `Helper::header(...)` | Set HTTP header |
| `Helper::exit()` | Terminate request |
| `Helper::Parameter($key)` | Get route parameter |
| `Helper::PageNo()` | Get current page number |
| `Helper::PageHtml($total)` | Generate pagination HTML |

### Session Management (Pure State Container)

Session belongs to the Controller layer's responsibility. The recommended `Controller\Session` class only does state storage and retrieval, **does not call any other classes**.

```php
<?php
namespace MyProject\Controller;

use DuckPhp\Foundation\SimpleSessionTrait;

class Session
{
    use SimpleSessionTrait;  // Built-in lazy session_start() + get/set/unset
    
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

### Action (Controller Common Functionality)

#### Why Action is Needed

Controller routing methods (`action_xxx`) often require repetitive orchestration logic. For example:

- Multiple routes need to "get the currently logged-in user"
- Login and registration both need "verify credentials → write to Session"
- Multiple Controllers may use the same set of orchestration

Extract these common logics into **Action classes** like `Controller\UserAction`, keeping routing methods thin.

#### Action Example

```php
<?php
namespace MyProject\Controller;

use MyProject\Business\UserBusiness;
use MyProject\Controller\Session;

class UserAction
{
    // Login: verify credentials + write to Session
    public function login(string $username, string $password): array
    {
        $user = UserBusiness::_()->login($username, $password);
        Session::_()->setUserId($user['id']);
        return $user;
    }
    
    // Get current user: read Session → query data through Business
    public function getCurrentUser(): ?array
    {
        $id = Session::_()->getUserId();
        return $id ? UserBusiness::_()->getUser($id) : null;
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

When to use Action: When the same set of orchestration is shared by **multiple routing methods** or **multiple Controllers**.

#### Delegating Routing Methods to Action

```php
class MainController
{
    public function action_login()
    {
        if (UserAction::_()->isLoggedIn()) {
            Helper::Show302(''); Helper::exit();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                UserAction::_()->login(
                    trim(Helper::POST('username', '')),
                    Helper::POST('password', '')
                );
                Helper::Show302(''); Helper::exit();
            } catch (\Exception $ex) {
                $error = $ex->getMessage();
            }
        }
        Helper::Show(get_defined_vars(), 'user_login');
    }
    
    public function action_profile()
    {
        $user = UserAction::_()->getCurrentUser();
        if (!$user) { Helper::Show302('login'); Helper::exit(); }
        Helper::Show(get_defined_vars(), 'user_profile');
    }
    
    public function action_logout()
    {
        UserAction::_()->logout();
        Helper::Show302(''); Helper::exit();
    }
}
```

#### Three-Layer Responsibility Comparison

| Class | Position | Can Call | Cannot Call |
|---|---|---|---|
| `Session` | State container | None (pure container) | — |
| `UserAction` | Controller common functionality | `Session`, `Business`, `Helper` | **❌ Model** |
| `MainController` | Route entry | `Action`, `Business`, `Helper` | — |

#### Call Chain

```
Registration:
  MainController::action_register()
    → UserAction::register()           ← Orchestration
        → Business::register()         ← Pure data validation + storage
        → Session::setUserId()         ← Store state

View profile:
  MainController::action_profile()
    → UserAction::getCurrentUser()     ← Orchestration
        → Session::getUserId()         ← Read state
        → Business::getUser()          ← Query data through Business
```

### Reading Settings and Config

```php
// Read settings from DuckPhpSettings.config.php
Helper::Setting('database_list');

// Read configuration from config/{name}.php
Helper::Config('app', 'key');
```

## Business Layer

The Business layer is a middle layer that DuckPHP **strongly emphasizes**, responsible for business logic orchestration.

### Core Principle: Stateless

The Business layer must be **purely stateless** — it does not depend on any request context, does not read or write Session, and does not operate on superglobal variables like `$_GET`/`$_POST`/`$_SERVER`.

When multiple Business classes need to share the same logic, extract it into a **Service** class. Service is located in the Business layer, **can call Model and other Services**.

```
Controller Layer (Stateful)
  ├─ Parse HTTP input
  ├─ Manage Session / login status
  ├─ Decide output format
  └─ Call Business
       │
       ▼
Business Layer (Purely Stateless) ✓
  ├─ Business rules and validation
  ├─ Call Model to read/write data
  ├─ Return pure data results
  └─ ❌ Do not touch $_GET / $_POST
       │
       ▼
Model Layer (Stateless)
  └─ Data access
```

### Correct Example

```php
<?php
namespace MyProject\Business;

use DuckPhp\Foundation\Business\Base;

class MyBusiness extends Base
{
    public function getList()
    {
        // Call Model to get data
        $data = MyModel::_()->getAll();
        
        // Assemble business logic
        $processed = array_map(function ($item) {
            $item['display_name'] = strtoupper($item['name']);
            return $item;
        }, $data);
        
        return $processed;
    }
}
```

Call as singleton:

```php
MyBusiness::_()->getList();    // Singleton in the current phase
```

### Business Helper Methods

| Method | Description |
|---|---|
| `Helper::Setting($key)` | Read global settings |
| `Helper::Config($file, $key)` | Read configuration file |
| `Helper::BusinessThrowOn($flag, $message)` | Conditionally throw business exception |
| `Helper::Cache()` | Get cache instance |
| `Helper::XpCall()` | Safe call (catches exceptions) |
| `Helper::AdminService()` | Get admin service |
| `Helper::UserService()` | Get user service |




```php
<?php
namespace MyProject\Business;

use MyProject\Model\LogModel;

class CommonService
{
    use \DuckPhp\Foundation\SimpleBusinessTrait;
    
    public function writeAuditLog(string $action, array $data): void
    {
        LogModel::_()->addLog([
            'action' => $action,
            'data' => json_encode($data),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',  // Note: Service also does not read/write Session
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
```

Calling Service in Business:

```php
class OrderBusiness
{
    public function createOrder(array $cart)
    {
        // ... order logic ...
        CommonService::_()->writeAuditLog('order_created', ['order_id' => $id]);
    }
}
```

#### Action and Service Correspondence

```
Controller Layer               Business Layer
─────────────────────────────────────────────
MainController               UserBusiness
  (Route entry)                (Business logic)
       │                           │
       ▼                           ▼
UserAction                     CommonService
  (Controller common functionality) (Business common functionality)
  → Call Business + Session        → Call Model + Business
  → ❌ Do not call Model           → ❌ Do not touch Session
```

## Model Layer

The Model layer is responsible for data access.

Models are organized by **data tables**. Its responsibilities are:

- Encapsulate all database operations related to the current table.
- Provide parameterized queries to prevent SQL injection.
- Return raw data (arrays), without business judgment.

**Do not write business rules or throw exceptions in Model.**

### Inheriting Foundation\Model\Base (Recommended)

```php
<?php
namespace MyProject\Model;

use DuckPhp\Foundation\Model\Base;

class DemoModel extends Base
{
    // Auto table name: demo (class name without "Model" suffix, lowercased)
    // Can be overridden via $table_name
    // Can specify prefix via $table_prefix
}
```

### ⚠️ Important Note: Method Visibility

The built-in methods provided by `Foundation\Model\Base` (through `SimpleModelTrait`) are **all `protected`**, and cannot be called directly from outside the Model. The correct approaches are:

1. **Expose public methods in Model subclasses** (recommended)
2. **Directly use the `Db()` method of the `Helper` class to operate the database**

### Method 1: Expose Public Methods in Model

```php
<?php
namespace MyProject\Model;

use DuckPhp\Foundation\Model\Base;

class DemoModel extends Base
{
    // Encapsulate public methods in model subclasses
    public function findUser($id)
    {
        return $this->find($id);  // find() is protected, can only be called internally
    }
    
    public function findUserBy(array $condition)
    {
        return $this->find($condition);
    }
    
    public function addUser(array $data)
    {
        return $this->add($data);
    }
    
    public function updateUser($id, array $data)
    {
        return $this->update($id, $data, 'id');
    }
    
    public function getUserList(array $where = [], int $page = 1, int $page_size = 10): array
    {
        return $this->getList($where, $page, $page_size); // Returns [$total, $data]
    }
    
    // SQL queries can also be encapsulated
    public function search($keyword)
    {
        return $this->fetchAll(
            "SELECT * FROM `'TABLE'` WHERE name LIKE ?",
            '%' . $keyword . '%'
        );
    }
}

// External calls
DemoModel::_()->findUser(1);
DemoModel::_()->addUser(['name' => 'foo', 'age' => 18]);
DemoModel::_()->updateUser(1, ['name' => 'bar']);
[$total, $data] = DemoModel::_()->getUserList([], 1, 10);
```

### Method 2: Directly Use Helper/Db

```php
use DuckPhp\Foundation\Model\Helper;

// Read-write separation
$rows = Helper::DbForRead()->fetchAll("SELECT * FROM users WHERE status=?", 1);
Helper::DbForWrite()->execute("UPDATE users SET name=? WHERE id=?", 'new', 1);

// Pagination
$sql = Helper::SqlForPager("SELECT * FROM users", $page, 10);
$sql = Helper::SqlForCountSimply("SELECT * FROM users");
```

### Model Built-in Methods Quick Reference (protected, for internal Model use only)

| Method | Description |
|---|---|
| `find($id)` / `find($condition)` | Find by primary key or condition |
| `add($data)` | Insert data (associative array) |
| `update($id, $data, $pk)` | Update data |
| `getList($where, $page, $size)` | Paginated list, returns `[$total, $data]` |
| `fetchAll($sql, ...$args)` | Query multiple rows |
| `fetch($sql, ...$args)` | Query single row |
| `fetchColumn($sql, ...$args)` | Query single column value |
| `fetchObject($sql, ...$args)` | Query single row object |
| `fetchObjectAll($sql, ...$args)` | Query multiple row objects |
| `execute($sql, ...$args)` | Execute SQL |
| `prepare($sql)` | Replace `'TABLE'` placeholder with actual table name |
| `table()` | Get full table name (prefix + name) |

The `'TABLE'` placeholder is automatically replaced with the actual table name (`$table_prefix . $table_name`).

### Not Inheriting Base, Directly Use Db

```php
use DuckPhp\Foundation\Model\Helper;

// Get database connection
$db = Helper::DbForRead();    // Read connection
$db = Helper::DbForWrite();   // Write connection

// Direct SQL
$rows = Helper::DbForRead()->fetchAll("SELECT * FROM users WHERE status=?", 1);
Helper::DbForWrite()->execute("UPDATE users SET name=? WHERE id=?", 'foo', 1);

// Pagination SQL
$sql = Helper::SqlForPager("SELECT * FROM users", $pageNo, $pageSize);
$sql = Helper::SqlForCountSimply("SELECT * FROM users"); // Automatically converted to COUNT(*)
```

### Model Helper Methods

| Method | Description |
|---|---|
| `Helper::Db($tag)` | Get specified database connection |
| `Helper::DbForRead()` | Get read connection |
| `Helper::DbForWrite()` | Get write connection |
| `Helper::SqlForPager($sql, $page, $size)` | Add pagination to SQL |
| `Helper::SqlForCountSimply($sql)` | Convert SQL to COUNT query |

## View Layer

Views are ordinary PHP files placed in the `view/` directory.

### View File Locations

```
view/
├── main.php                  # View for MainController/action_index
├── my/
│   └── index.php             # View for MyController/action_index
└── _sys/
    ├── error_404.php         # 404 error page
    └── error_500.php         # 500 error page
```

### View Content

```php
<?php
// view/main.php
// Variables in the $data array are automatically expanded as PHP variables
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= __h($title) ?></title>
</head>
<body>
    <h1><?= $content ?></h1>
    <a href="<?= __url('user/login') ?>">Login</a>
</body>
</html>
```

### Header and Footer

```php
// Set in controller constructor
public function __construct()
{
    Helper::setViewHeadFoot('_sys/header', '_sys/footer');
}

// Or directly assign
Helper::assignViewData('site_name', 'MySite');
```

### View Rendering Methods

| Method | Description |
|---|---|
| `Helper::Show($data, $view)` | Render view (with header and footer) |
| `Helper::Display($view, $data)` | Render view fragment (without header and footer) |
| `Helper::Render($view, $data)` | Render as string |

View file lookup rules:
1. If `$view` is empty, automatically infer using `{ControllerClass}/{actionMethod}`
2. If `$view` does not end with `.php`, automatically append it
3. In multi-app nesting, child apps can override parent app views

### Switching View Engine

You can switch the view engine through extensions:

```php
$options = [
    'ext' => [
        \DuckPhp\Ext\CallableView::class => true,  // Functional view
        \DuckPhp\Ext\JsonView::class => true,       // JSON view
    ],
];
```

- **CallableView**: Use class methods instead of view files, suitable for API interfaces or simple projects
- **JsonView**: All views automatically output as JSON, suitable for pure API projects
