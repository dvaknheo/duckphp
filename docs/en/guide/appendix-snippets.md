# Appendix B · Code Snippets

> Purpose: snippets you can **copy straight into your project**, indexed by "the thing you want to do". Each one notes the chapter where it is expanded, and whether it is "runnable in the repo" or an "illustrative pattern".
> Namespace convention: `MyProj`. Sample assets: `demo/` (multi-entry sample app, run by `tests/ZAllDemoTest.php`), `skeleton/` (the scaffold skeleton), `tests/data_for_tests/ZThirdDemo` (the sample project for volume 3).
> ⚠️ Snippets marked ⚠️ are **illustrative**: there is no ready-made example for them in the repo (the framework does not ship that capability either; you implement it yourself).

## 1. CRUD (model layer)

From `demo/public/dbtest.php` (runnable) and [Chapter 2-8](../guide/model.md).

```php
namespace MyProj\Model;

class NoteModel extends Base
{
    public function __construct() { $this->table_name = 'note'; }   // table name (without the prefix)

    public function create(array $data): int
    {
        $sql = "insert into `'TABLE'` (title, content) values(?, ?)";
        $this->execute($sql, $data['title'], $data['content']);     // goes to the write connection
        return (int)Helper::Db()->lastInsertId();
    }
    public function findById(int $id): ?array
    {
        return $this->fetch("select * from `'TABLE'` where id=?", $id) ?: null;
    }
    public function updateById(int $id, array $data): bool
    {
        $sql = "update `'TABLE'` set title=?, content=? where id=?";
        return $this->execute($sql, $data['title'], $data['content'], $id) > 0;
    }
    public function deleteById(int $id): bool
    {
        return $this->execute("delete from `'TABLE'` where id=? limit 1", $id) > 0;
    }
}
```

Key points: `` `'TABLE'` `` is the table-name macro (it auto-prepends `table_prefix`); the CRUD methods of [`ModelTrait`](../reference/Foundation-Model-ModelTrait.md) are `protected`, so you open your own public methods ([Chapter 2-8](../guide/model.md)).

## 2. Pagination (list + pager)

From `demo/public/dbtest.php` (runnable) and [Chapter 2-7](../guide/database.md).

```php
// in the model
public function paginate(int $page, int $size = 10): array
{
    $sql   = "select * from `'TABLE'` order by id desc";
    $total = $this->fetchColumn(Helper::SqlForCountSimply($sql));
    $list  = $this->fetchAll(Helper::SqlForPager($sql, $page, $size));
    return [$total, $list];
}
```

```php
// in the controller
list($total, $list) = NoteBusiness::_()->paginate(Helper::PageNo(), Helper::PageWindow(3));
$pager = Helper::PageHtml($total);
Helper::Show(get_defined_vars(), 'note/list');
```

## 3. Form Submit + Validation + Re-Display

See [Chapter 2-5](../guide/controllers.md) and [Chapter 2-10](../guide/validator.md).

```php
public function create()
{
    if (!Helper::IsPost()) {
        Helper::Show([], 'note/form');
        return;
    }
    $errors = Helper::Validator()->init([
        'title' => 'required|maxLen:64',
    ])->valid(Helper::POST());
    if ($errors) {
        Helper::Show(['errors' => $errors, 'post' => Helper::POST()], 'note/form');
        return;
    }
    $id = NoteBusiness::_()->create(Helper::POST());
    Helper::Show302(Helper::Url('note/show?id=' . $id));
}
```

## 4. JSON Endpoint

See [Chapter 2-5](../guide/controllers.md).

```php
public function list()
{
    $page = (int)Helper::GET('page', 1);
    [$total, $rows] = NoteBusiness::_()->paginate($page);
    Helper::ShowJson(['code' => 0, 'total' => $total, 'data' => $rows]);
}
```

> To wrap success/failure in one uniform format, wrap it in a route hook, or write an `ApiControllerBase` ([Chapter 2-4 Route Hooks](../guide/route-hooks.md)).

## 5. Login / Logout / Current User

For usage see [Chapter 2-19 Using the User System](../guide/user.md); the options below belong to the **integration** configuration, fully documented in [Chapter 4-11 Implementing the User System](../guide/impl-user.md) ([`GlobalUser`](../reference/GlobalUser-GlobalUser.md) outsources the implementation to your project classes through **callbacks**).

```php
// options in System/App.php
'globaluser_url_login'    => 'user/login',
'globaluser_url_logout'   => 'user/logout',
'globaluser_url_register' => 'user/register',
'globaluser_login_session' => [MyProj\UserSystem\UserSession::class, '_'],
'globaluser_login_service' => [MyProj\UserSystem\UserService::class, '_'],
'globaluser_local_service' => [MyProj\UserSystem\UserService::class, '_'],
```

```php
// in the controller
$userId = Helper::UserId();          // when not logged in it goes through throwLoginOn(): a 302 to the login page / JSON for Ajax, then exit()
Helper::Show(get_defined_vars(), 'user/center');

// to take over the redirect yourself: pass false; not logged in returns 0 (no exit)
$userId = Helper::UserId(false);
if (!$userId) {
    Helper::Show302(Helper::User()->urlForLogin('user/center'));
    return;
}
```

⚠️ Three keys are **required**: `globaluser_login_session` (who is currently logged in), `globaluser_login_service` (register/login/logout), `globaluser_local_service` (`canAccess()`/`log()`/`batchGetUsernames()`); missing any of them throws `need ext options '…'`. On the admin side, replace `globaluser_` with `globaladmin_` (without the register part).

## 6. Permission Checks (admin + resource ownership)

```php
// 1) is it an administrator: leave it to the permission system (AdminControllerBase automatically checks install state and login)
class AdminController extends \DuckPhp\Foundation\Controller\AdminControllerBase { }

// 2) does this record belong to them: you must judge it yourself in the Business layer (the framework does not provide this)
public function edit(int $noteId, int $userId): array
{
    $note = NoteModel::_()->findById($noteId);
    Helper::BusinessThrowOn(!$note || (int)$note['user_id'] !== $userId, '无权操作', 403);
    return $note;
}
```

The admin menu is generated from controller annotations (`@menu_directory` etc.), see [Chapter 2-20](../guide/admin.md) and [Ext\PermissionMenu](../reference/Ext-PermissionMenu.md).

## 7. Caching Hot Data

See [Chapter 2-14](../guide/cache.md).

```php
public function hotProducts(): array
{
    $key  = 'hot_products';
    $data = Helper::Cache()->get($key);
    if ($data === null) {
        $data = ProductModel::_()->getHot(20);
        Helper::Cache()->set($key, $data, 300);      // 5-minute TTL
    }
    return $data;
}
```

## 8. Transactions

See [Chapter 2-7](../guide/database.md).

```php
$pdo = Helper::Db()->PDO();
try {
    $pdo->beginTransaction();
    OrderModel::_()->create($order);
    OrderItemModel::_()->createMany($items);
    $pdo->commit();
} catch (\Throwable $ex) {
    $pdo->rollBack();
    throw $ex;                    // hand over to the exception machinery (Chapter 2-12)
}
```

## 9. Cross-App Calls (multi-app projects)

See [Chapter 3-4](../guide/component-sharing.md), [Chapter 3-1](../guide/advanced-phase.md).

```php
// way one: switch the phase and call (within the same process)
$child = \MyProj\System\App::_()->toThisChild(\MyProj\Shop\System\App::class);
$price = $child->getPrice($id);

// way two: a phase proxy (pins the call onto a given phase)
$proxy = \DuckPhp\Component\PhaseProxy::_('shop');
$price = $proxy->getPrice($id);

// way three: broadcast an event, whoever cares listens
Helper::FireGlobalEvent('order.created', $orderId);
```

## 10. Adding Something to Every Response (hooks)

See [Chapter 2-4 Route Hooks](../guide/route-hooks.md).

```php
// in onInited() of src/System/App.php
Helper::addRouteHook(function (string $path_info) {
    Helper::header('X-Frame-Options: SAMEORIGIN');
    return false;                       // only adds a header, does not intercept
}, 'prepend-outter');
```

## 11. Forcing HTTPS ⚠️

The framework does not provide this; write a pre hook yourself ([Chapter 2-18](../guide/security-performance.md)):

```php
Helper::addRouteHook(function (string $path_info) {
    if (App::_()->isCli() || !empty($_SERVER['HTTPS'])) { return false; }
    Helper::Show302('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    return true;                        // intercepted
}, 'prepend-outter');
```

## 12. CSRF Token ⚠️

The framework **does not provide** this; implement it yourself ([Chapter 2-18](../guide/security-performance.md)):

```php
// first wrap two public methods in the session class (the Trait's get/set are protected, see Chapter 2-11):
//   public function setCsrfToken(string $t): void { $this->set('csrf_token', $t); }
//   public function getCsrfToken(): string { return (string)$this->get('csrf_token'); }

// issuing the token (in the controller)
$token = bin2hex(random_bytes(16));
Session::_()->setCsrfToken($token);
Helper::Show(['csrf' => $token], 'note/form');

// validating (at the POST entry)
Helper::BusinessThrowOn(
    !hash_equals(Session::_()->getCsrfToken(), (string)Helper::POST('_token')),
    '会话已过期，请重新提交', 419
);
```

## 13. File Upload ⚠️

Read the data with `Helper::FILES()`; **validation and writing to disk are yours to write** ([Chapter 2-18](../guide/security-performance.md)):

```php
$file = Helper::FILES('avatar');
Helper::BusinessThrowOn(($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK, '上传失败');
Helper::BusinessThrowOn($file['size'] > 2 * 1024 * 1024, '文件过大');
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
Helper::BusinessThrowOn(!in_array($ext, ['jpg', 'png'], true), '只允许 jpg/png');
$name = bin2hex(random_bytes(8)) . '.' . $ext;          // rename it; do not use the original file name
move_uploaded_file($file['tmp_name'], Helper::PathOfRuntime() . 'upload/' . $name);
```

## 14. Scheduled Tasks

See [Chapter 2-16](../guide/cli.md).

```php
class JobCommands
{
    /** @command_desc 清理过期便签 */
    public function command_clean()
    {
        $n = NoteBusiness::_()->cleanExpired();
        echo "cleaned: {$n}\n";
    }
}
```

```cron
*/10 * * * * cd /srv/myproj && /usr/bin/php cli.php clean >> runtime/cron.log 2>&1
```

## 15. Migration / Creating Tables

The approach in `demo/public/dbtest.php` (runnable): put the table-creation SQL in the model's `init()`, called once by the install flow or at controller construction; to export SQL use [`Ext\SqlDumper`](../reference/Ext-SqlDumper.md) ([Chapter 2-7](../guide/database.md), [Chapter 3-6](../guide/installer.md)).

```php
public function init()
{
    $sql = <<<EOT
CREATE TABLE IF NOT EXISTS `'TABLE'` (
  "id" INTEGER NOT NULL,
  "title" TEXT,
  PRIMARY KEY("id" AUTOINCREMENT)
);
EOT;
    $this->execute($sql);
}
```

---

> Can't find the snippet you want? First check the "symptom → troubleshooting path" section of the [Chapter 4-9 Troubleshooting Manual](../guide/troubleshooting.md), then look up the concrete class in `docs/zh/reference/`. To contribute a snippet: add it under the principle "runnable + source noted" (repo convention: examples only use existing assets).