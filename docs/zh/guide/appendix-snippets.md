# 附录 B · 代码片段库

> 用途：**能直接抄进工程**的片段，按「要做的事」索引。每段都标了它在哪一章被展开，以及是"仓库里能跑的"还是"示意写法"。
> 命名空间约定：`MyProj`。示例资产：`demo/`、`tests/data_for_tests/ZAllDemo`、`tests/data_for_tests/ZThirdDemo`。
> ⚠️ 标 ⚠️ 的片段是**示意**：仓库里没有现成示例对应它（框架也不内置该能力，需要你自己实现）。

## 1. CRUD（模型层）

来自 `demo/public/dbtest.php`（可跑）与[第 13 章](../guide/model.md)。

```php
namespace MyProj\Model;

class NoteModel extends Base
{
    public function __construct() { $this->table_name = 'note'; }   // 表名（不含前缀）

    public function create(array $data): int
    {
        $sql = "insert into `'TABLE'` (title, content) values(?, ?)";
        $this->execute($sql, $data['title'], $data['content']);     // 走写连接
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

要点：`` `'TABLE'` `` 是表名宏（自动加 `table_prefix`）；`ModelTrait` 的 CRUD 是 `protected`，所以要自己开 public 方法（[第 13 章](../guide/model.md)）。

## 2. 分页（列表 + 分页条）

来自 `demo/public/dbtest.php`（可跑）与[第 12 章](../guide/database.md)。

```php
// 模型里
public function paginate(int $page, int $size = 10): array
{
    $sql   = "select * from `'TABLE'` order by id desc";
    $total = $this->fetchColumn(Helper::SqlForCountSimply($sql));
    $list  = $this->fetchAll(Helper::SqlForPager($sql, $page, $size));
    return [$total, $list];
}
```

```php
// 控制器里
list($total, $list) = NoteBusiness::_()->paginate(Helper::PageNo(), Helper::PageWindow(3));
$pager = Helper::PageHtml($total);
Helper::Show(get_defined_vars(), 'note/list');
```

## 3. 表单提交 + 校验 + 回显

见[第 10 章](../guide/controllers.md)与[第 15 章](../guide/validator.md)。

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

## 4. JSON 接口

见[第 10 章](../guide/controllers.md)。

```php
public function list()
{
    $page = (int)Helper::GET('page', 1);
    [$total, $rows] = NoteBusiness::_()->paginate($page);
    Helper::ShowJson(['code' => 0, 'total' => $total, 'data' => $rows]);
}
```

> 想统一包装成功/失败格式，用路由钩子包一层，或写个 `ApiControllerBase`（[第 17 章](../guide/lifecycle.md)）。

## 5. 登录 / 登出 / 当前用户

配置见[第 16 章](../guide/external-auth.md)（`GlobalUser` 用**回调**把实现外包给工程类）。

```php
// System/App.php 的选项
'user_url_login'    => 'user/login',
'user_url_logout'   => 'user/logout',
'user_url_register' => 'user/register',
'user_callback_for_session'       => [MyProj\UserSystem\UserSession::class, '_'],
'user_callback_for_login_service' => [MyProj\UserSystem\UserService::class, '_'],
```

```php
// 控制器里
$userId = Helper::UserId();          // 未登录会抛 UserException（可被 302 到登录页接管）
Helper::Show(get_defined_vars(), 'user/center');

Helper::Show302(Helper::Url('user/logout'));   // 登出走后者的路由
```

⚠️ 旧文档里的 `user_callback_get_id/name/data/service` **已失效**，现在是 `user_callback_for_id/name/data/local_service`。

## 6. 权限判断（后台 + 资源归属）

```php
// ① 是不是管理员：交给权限体系（AdminControllerBase 会自动检查安装状态与登录）
class AdminController extends \DuckPhp\Foundation\Controller\AdminControllerBase { }

// ② 这条数据是不是他的：必须在 Business 层自己判断（框架不提供）
public function edit(int $noteId, int $userId): array
{
    $note = NoteModel::_()->findById($noteId);
    Helper::BusinessThrowOn(!$note || (int)$note['user_id'] !== $userId, '无权操作', 403);
    return $note;
}
```

后台菜单靠控制器注释生成（`@menu_directory` 等），见[第 16 章](../guide/external-auth.md)与 [Ext\PermissionMenu](../reference/Ext-PermissionMenu.md)。

## 7. 缓存热点数据

见[第 20 章](../guide/cache.md)。

```php
public function hotProducts(): array
{
    $key  = 'hot_products';
    $data = Helper::Cache()->get($key);
    if ($data === null) {
        $data = ProductModel::_()->getHot(20);
        Helper::Cache()->set($key, $data, 300);      // 5 分钟 TTL
    }
    return $data;
}
```

## 8. 事务

见[第 12 章](../guide/database.md)。

```php
$pdo = Helper::Db()->PDO();
try {
    $pdo->beginTransaction();
    OrderModel::_()->create($order);
    OrderItemModel::_()->createMany($items);
    $pdo->commit();
} catch (\Throwable $ex) {
    $pdo->rollBack();
    throw $ex;                    // 交给异常机制（第 18 章）
}
```

## 9. 跨应用调用（多应用项目）

见[第 28 章](../guide/component-sharing.md)、[第 25 章](../guide/advanced-phase.md)。

```php
// 方式一：切相位调用（同一个进程里）
$child = \MyProj\System\App::_()->toThisChild(\MyProj\Shop\System\App::class);
$price = $child->getPrice($id);

// 方式二：相位代理（把调用固定到某个相位执行）
$proxy = \DuckPhp\Component\PhaseProxy::_('shop');
$price = $proxy->getPrice($id);

// 方式三：广播事件，谁关心谁监听
Helper::FireGlobalEvent('order.created', $orderId);
```

## 10. 统一给响应加东西（钩子）

见[第 17 章](../guide/lifecycle.md)。

```php
// src/System/App.php 的 onInited() 里
Helper::addRouteHook(function (string $path_info) {
    Helper::header('X-Frame-Options: SAMEORIGIN');
    return false;                       // 只是加个头，不拦截
}, 'prepend-outter');
```

## 11. 强制 HTTPS ⚠️

框架不提供，自己写 pre 钩子（[第 24 章](../guide/security-performance.md)）：

```php
Helper::addRouteHook(function (string $path_info) {
    if (App::_()->isCli() || !empty($_SERVER['HTTPS'])) { return false; }
    Helper::Show302('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    return true;                        // 拦住
}, 'prepend-outter');
```

## 12. CSRF 令牌 ⚠️

框架**不提供**，需要自己实现（[第 24 章](../guide/security-performance.md)）：

```php
// 发令牌（控制器里）
$token = bin2hex(random_bytes(16));
Session::_()->set('csrf_token', $token);
Helper::Show(['csrf' => $token], 'note/form');

// 校验（POST 入口）
Helper::BusinessThrowOn(
    !hash_equals((string)Session::_()->get('csrf_token'), (string)Helper::POST('_token')),
    '会话已过期，请重新提交', 419
);
```

## 13. 文件上传 ⚠️

取数据用 `Helper::FILES()`，**校验与落盘要自己写**（[第 24 章](../guide/security-performance.md)）：

```php
$file = Helper::FILES('avatar');
Helper::BusinessThrowOn(($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK, '上传失败');
Helper::BusinessThrowOn($file['size'] > 2 * 1024 * 1024, '文件过大');
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
Helper::BusinessThrowOn(!in_array($ext, ['jpg', 'png'], true), '只允许 jpg/png');
$name = bin2hex(random_bytes(8)) . '.' . $ext;          // 重命名，别用原文件名
move_uploaded_file($file['tmp_name'], Helper::PathOfRuntime() . 'upload/' . $name);
```

## 14. 定时任务

见[第 22 章](../guide/cli.md)。

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

## 15. 迁移/建表

`demo/public/dbtest.php` 里的做法（可跑）：把建表 SQL 写在模型的 `init()` 里，由安装流程或控制器构造时调一次；需要导出 SQL 用 `Ext\SqlDumper`（[第 12 章](../guide/database.md)、[第 30 章](../guide/installer.md)）。

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

> 找不到你要的片段？先看[第 40 章 排错手册](../guide/troubleshooting.md)的「症状 → 排查路径」，再去 `docs/zh/reference/` 查具体类。想补片段：按「能跑 + 标明出处」的原则加进来（本仓库约定：示例只用现有资产）。
