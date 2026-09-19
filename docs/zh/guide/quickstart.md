# 1-4 第一个页面

> 目标：做一个「便签列表」页，把**路由 → 控制器 → 业务 → 模型 → 视图**这条链走通一遍，之后所有功能都是它的变体。
> 前置：[第 1-3 章 目录结构与编码规则](project-structure.md)。预计 25 分钟。
> 示例写法在仓库里都有同类实现：`skeleton/src/`（脚手架）与 `tests/data_for_tests/ZAllDemo/src/`（有测试兜底的四层示例）。

## 做完是什么样

```
GET /Note/index     →  便签列表页
GET /Note/show?id=1 →  单条便签
```

## 步骤 1：建库建表

SQLite 最省事（换 MySQL 只是改 DSN）：

```sql
CREATE TABLE note (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  body TEXT,
  created_at TIMESTAMP
);
INSERT INTO note (title, body, created_at) VALUES ('第一条便签', '你好，DuckPHP', datetime('now'));
```

```bash
sqlite3 runtime/app.db < schema.sql
```

## 步骤 2：把数据库告诉框架

敏感信息放设置文件（为什么放这里见[第 1-5 章](configuration.md)）：

```php
<?php
// config/DuckPhpSettings.config.php
return [
    'database_list' => [
        [
            'dsn' => 'sqlite:' . __DIR__ . '/../runtime/app.db',
            // MySQL：'dsn' => 'mysql:host=127.0.0.1;dbname=demo;charset=utf8mb4;', 'username' => …, 'password' => …,
        ],
    ],
];
```

> [`DbManager`](../reference/Component-DbManager.md) 默认会从**设置**里取 `database_list`（选项 `database_list_reload_by_setting` 默认为真），所以只写在这里就够。

## 步骤 3：模型（Model）—— 只管数据

```php
<?php declare(strict_types=1);
namespace MyProj\Model;

use DuckPhp\Foundation\Model\Base;

class NoteModel extends Base
{
    // 类名 NoteModel → 表名 note（去掉 Model 再小写）；表名不同就 protected $table_name = 'notes';
    public function getRecent(int $limit = 20): array
    {
        $limit = (int) $limit;
        return static::Db()->fetchAll('SELECT * FROM `' . $this->table() . "` ORDER BY id DESC LIMIT $limit");
    }
    public function findOne(int $id): ?array
    {
        $row = static::Db()->fetch('SELECT * FROM `' . $this->table() . '` WHERE id = ?', $id);
        return $row ?: null;
    }
}
```

要点：

- [`static::Db()`](../reference/Db-Db.md) 来自 `Foundation\Model\Base`（不用再引 Helper）；读写分离时用 `DbForRead()` / `DbForWrite()`。
- `$this->table()` 给出「表前缀 + 表名」；`$this->prepare($sql)` 还能把 SQL 里的 `` `'TABLE'` `` 占位换成真实表名。
- Model 里**不写业务判断、不抛异常**（铁律，见上一章）。

## 步骤 4：业务（Business）—— 放规则

```php
<?php declare(strict_types=1);
namespace MyProj\Business;

use DuckPhp\Foundation\SingletonTrait;
use MyProj\Model\NoteModel;

class NoteBusiness
{
    use SingletonTrait;

    public function recentNotes(int $limit = 20): array
    {
        return NoteModel::_()->getRecent($limit);
    }
    public function noteOr404(int $id): array
    {
        $note = NoteModel::_()->findOne($id);
        Helper::BusinessThrowOn(!$note, '便签不存在', 404);   // 业务层的条件抛（第 2-11 章）
        return $note;
    }
}
```

`Helper` 是业务层自己的助手（`src/Business/Helper.php`，内部 [`use DuckPhp\Helper\BusinessHelperTrait;`](../reference/Helper-BusinessHelperTrait.md)），脚手架自带。

## 步骤 5：控制器（Controller）—— 收输入、出输出

```php
<?php declare(strict_types=1);
namespace MyProj\Controller;

use MyProj\Business\NoteBusiness;

class NoteController extends Base
{
    public function index()
    {
        $limit = (int) Helper::GET('limit', 20);          // 输入只在这层碰
        $list  = NoteBusiness::_()->recentNotes($limit);  // 逻辑交给业务层
        Helper::Show(get_defined_vars(), 'note/index');   // 输出：渲染视图
    }
    public function show()
    {
        $note = NoteBusiness::_()->noteOr404((int) Helper::GET('id'));
        Helper::Show(get_defined_vars(), 'note/show');
    }
}
```

输出一共四种（[第 2-3 章](controllers.md)会展开）：`Helper::Show($data, 'view')` 渲染视图、`Helper::ShowJson($data)` 出 JSON、`Helper::Show302($url)` 跳转、`Helper::Show404()` 出 404。

## 步骤 6：视图（View）—— 只做展示

```php
<?php // view/note/index.php ?>
<!doctype html>
<html><head><meta charset="utf-8"><title>便签</title></head><body>
<h1>便签（<?= count($list) ?> 条）</h1>
<ul>
<?php foreach ($list as $note): ?>
    <li>
        <a href="<?= __url('Note/show?id=' . $note['id']) ?>"><?= __h($note['title']) ?></a>
        <small><?= __h($note['created_at']) ?></small>
    </li>
<?php endforeach; ?>
</ul>
</body></html>
```

视图里只用全局函数：`__h()` 转义、`__url()` 生成 URL、`__res()` 生成资源 URL、`__l()` 翻译。

## 步骤 7：跑起来

```bash
php -S 127.0.0.1:8080 -t public
```

| 访问 | 结果 |
|---|---|
| `http://127.0.0.1:8080/Note/index` | 列表页 |
| `http://127.0.0.1:8080/Note/show?id=1` | 详情页 |
| `http://127.0.0.1:8080/` | 走 `MainController::index()`（欢迎页控制器，第 2-2 章） |

> URL 里的 `Note` 大小写要与类名一致（默认不做大小写宽松处理）。

## 加个「新建便签」练手

```php
// Controller：只搬运输入与输出
public function add()
{
    if (Helper::POST('title')) {
        NoteBusiness::_()->create(Helper::POST());
        Helper::Show302('Note/index');
    }
    Helper::Show([], 'note/add');
}

// Business：规则与校验放这里
public function create(array $post): int
{
    Helper::BusinessThrowOn(trim((string) ($post['title'] ?? '')) === '', '标题不能为空', 1001);
    return NoteModel::_()->create($post);
}
```

表单校验的完整做法（过滤器 + 错误数组）见[第 2-8 章](validator.md)；这里只求把链路走通。

## 单文件版（不建工程也能跑）

只验证「框架能跑」时，`demo/public/helloworld.php` 是最小可运行例子（`ZAllDemoTest` 会请求它并比对输出）：

```php
<?php declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

class MainController          // 单文件示例：控制器写在同一文件里，所以把控制器命名空间设为根
{
    public function index() { echo 'hello world'; }
}

\DuckPhp\DuckPhp::RunQuickly([
    'is_debug' => true,
    'namespace_controller' => '\\',
]);
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `/Note/index` 404 | 方法带了 `action_` 前缀而选项里没配 | 方法名去掉前缀，或配 `'controller_method_prefix' => 'action_'` |
| `Class "MyProj\Model\NoteModel" not found` | 命名空间与目录不匹配 | `src/Model/NoteModel.php` + `namespace MyProj\Model;` |
| 视图里 `$list` 未定义 | 没把变量传进去 | `Helper::Show(get_defined_vars(), 'note/index')` |
| 列表页报 SQL 错误 | 表没建 / DSN 指到别处 | 检查 `runtime/app.db` 与设置文件里的路径 |
| 页面乱码 | 视图没声明编码 | HTML 里加 `<meta charset="utf-8">` |
| 便签存在却报「便签不存在」 | `Helper::GET('id')` 拿到的不是数字 | 强转 `(int)`，并在 Business 层校验 |

## 下一步

- [第 1-5 章 配置与设置](configuration.md)：把 `App.php` 的选项与 `config/` 的设置彻底分清。
- [第 2-3 章 控制器](controllers.md)、[第 2-4 章 视图与模板](views.md)：这两层的完整能力。
- [第 2-10 章 请求生命周期与钩子点](lifecycle.md)：刚才这一次请求，框架内部都做了什么（想让框架在中间插一手时回来看）。
