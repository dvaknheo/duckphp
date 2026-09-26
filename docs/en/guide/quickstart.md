# 1-4 Your First Page

> Goal: build a "note list" page and walk the **routing → controller → business → model → view** chain end to end; every feature after this is a variant of it.
> Prerequisites: [Chapter 1-3 Directory Structure and the Four Layers](project-structure.md). About 25 minutes.
> Everything shown here has a counterpart in the repo: `skeleton/src/` (the four-layer skeleton the scaffold generates, which this chapter follows) and `demo/` (a runnable multi-entry sample app that `tests/ZAllDemoTest.php` boots against a built-in server).

## What you'll build

```
GET /Note/index     →  便签列表页
GET /Note/show?id=1 →  单条便签
```

## Step 1: create the database and table

SQLite is the easiest (switching to MySQL is just a DSN change):

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

## Step 2: tell the framework about the database

Sensitive information goes in the settings file (why here: see [Chapter 1-5](configuration.md)):

```php
<?php
// config/DuckPhpSettings.config.php
return [
    'database_list' => [
        [
            'dsn' => 'sqlite:' . __DIR__ . '/../runtime/app.db',
            // MySQL: 'dsn' => 'mysql:host=127.0.0.1;dbname=demo;charset=utf8mb4;', 'username' => …, 'password' => …,
        ],
    ],
];
```

> [`DbManager`](../reference/Component-DbManager.md) reads `database_list` from the **settings** by default (the `database_list_reload_by_setting` option defaults to true), so writing it here is enough.

## Step 3: the Model — data only

```php
<?php declare(strict_types=1);
namespace MyProj\Model;

use DuckPhp\Foundation\Model\Base;

class NoteModel extends Base
{
    // class name NoteModel → table name note (drop Model, lowercase); if the table differs: protected $table_name = 'notes';
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

Key points:

- [`static::Db()`](../reference/Db-Db.md) comes from `Foundation\Model\Base` (no need to import Helper); with read-write splitting use `DbForRead()` / `DbForWrite()`.
- `$this->table()` gives "table prefix + table name"; `$this->prepare($sql)` also replaces the `` `'TABLE'` `` placeholder in SQL with the real table name.
- A Model **contains no business judgments and throws no exceptions** (an iron rule, see the previous chapter).

## Step 4: the Business — where rules live

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
        Helper::BusinessThrowOn(!$note, '便签不存在', 404);   // conditional throw from the business layer (Chapter 2-12)
        return $note;
    }
}
```

`Helper` is the business layer's own helper (`src/Business/Helper.php`, one line [`extends DuckPhp\Foundation\Business\BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md)), shipped by the scaffold.

**Its job is to pin down "what the business layer may use" as one class**: static methods like `Helper::BusinessThrowOn()` / `Helper::Config()` / `Helper::Cache()` just forward to the corresponding component (`CoreHelper`/`Configer`/`Cache`…), so business code doesn't `use` framework classes everywhere or `new` components directly, and tests can swap the underlying layer wholesale. At the same time **it only has this layer's methods**: writing `Helper::Show()` (output) or `Helper::GET()` (reading the request) here gives a plain "method does not exist" — that is not missing functionality but **a layer boundary expressed through the type system** ([Chapter 2-9](helper.md)). The full method list: see [Business\BusinessHelper](../reference/Foundation-Business-BusinessHelper.md).

## Step 5: the Controller — input in, output out

```php
<?php declare(strict_types=1);
namespace MyProj\Controller;

use MyProj\Business\NoteBusiness;

class NoteController extends Base
{
    public function index()
    {
        $limit = (int) Helper::GET('limit', 20);          // input is touched only in this layer
        $list  = NoteBusiness::_()->recentNotes($limit);  // logic goes to the business layer
        Helper::Show(get_defined_vars(), 'note/index');   // output: render the view
    }
    public function show()
    {
        $note = NoteBusiness::_()->noteOr404((int) Helper::GET('id'));
        Helper::Show(get_defined_vars(), 'note/show');
    }
}
```

There are four kinds of output ([Chapter 2-5](controllers.md) expands on them): `Helper::Show($data, 'view')` renders a view, `Helper::ShowJson($data)` outputs JSON, `Helper::Show302($url)` redirects, `Helper::Show404()` outputs a 404.

## Step 6: the View — display only

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

Views use global functions only: `__h()` escapes, `__url()` builds URLs, `__res()` builds resource URLs, `__l()` translates.

## Step 7: run it

```bash
php -S 127.0.0.1:8080 -t public
```

| Visit                                     | Result                                          |
| -------------------------------------- | ------------------------------------------- |
| `http://127.0.0.1:8080/Note/index`     | The list page                                         |
| `http://127.0.0.1:8080/Note/show?id=1` | The detail page                                         |
| `http://127.0.0.1:8080/`               | Goes to `MainController::index()` (the welcome controller, Chapter 2-3) |

> The `Note` in the URL must match the class name's case (no case-lenient handling by default).

## Practice: add a "create note" action

```php
// Controller: only ferries input and output
public function add()
{
    if (Helper::POST('title')) {
        NoteBusiness::_()->create(Helper::POST());
        Helper::Show302('Note/index');
    }
    Helper::Show([], 'note/add');
}

// Business: rules and validation live here
public function create(array $post): int
{
    Helper::BusinessThrowOn(trim((string) ($post['title'] ?? '')) === '', '标题不能为空', 1001);
    return NoteModel::_()->create($post);
}
```

The full approach to form validation (filters + error arrays) is in [Chapter 2-10](validator.md); here we just want the chain to work.

## Single-file version (no project needed)

When you only want to verify "the framework runs", `demo/public/helloworld.php` is the smallest runnable example (`ZAllDemoTest` requests it and compares the output):

```php
<?php declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

class MainController          // single-file example: the controller lives in the same file, so set the controller namespace to root
{
    public function index() { echo 'hello world'; }
}

\DuckPhp\DuckPhp::RunQuickly([
    'is_debug' => true,
    'namespace_controller' => '\\',
]);
```

## Common errors

| Symptom                                         | Cause                          | Fix                                                    |
| ------------------------------------------ | --------------------------- | ----------------------------------------------------- |
| `/Note/index` gives 404                          | The method has an `action_` prefix but the option isn't set     | Drop the prefix from the method name, or set `'controller_method_prefix' => 'action_'`  |
| `Class "MyProj\Model\NoteModel" not found` | Namespace and directory don't match                  | `src/Model/NoteModel.php` + `namespace MyProj\Model;` |
| `$list` undefined in the view                            | The variable wasn't passed in                     | `Helper::Show(get_defined_vars(), 'note/index')`      |
| SQL error on the list page                                | The table wasn't created / the DSN points elsewhere              | Check `runtime/app.db` and the path in the settings file                         |
| Garbled characters on the page                                       | The view didn't declare an encoding                     | Add `<meta charset="utf-8">` to the HTML                      |
| The note exists yet it says "note not found"                              | What `Helper::GET('id')` returns isn't a number | Cast to `(int)`, and validate in the Business layer                            |

## Next steps

- [Chapter 1-5 Options and Settings](configuration.md): draw the line once and for all between options in `App.php` and settings in `config/`.
- [Chapter 2-5 Controllers](controllers.md), [Chapter 2-6 Views and Templates](views.md): the full capabilities of these two layers.
- [Chapter 2-2 Request Lifecycle](lifecycle.md): what the framework did internally during the request you just made (come back when you want to slip something into the middle).
