# DuckPhp\Component\DbManager

`DuckPhp\Component\DbManager` 是 DuckPHP 的数据库连接管理器。它负责根据配置创建、复用和关闭数据库连接，支持读写分离和日志记录。

---

## 简介

`DbManager` 通常在 `DuckPhp\DuckPhp` 的默认组件列表中通过 `ext` 选项加载。它把 `database_list` 中的配置解析为多个 `DuckPhp\Db\Db` 对象（或自定义数据库类），并按 tag 编号缓存这些对象。写操作使用 tag `0`，读操作使用 tag `1`。如果未单独配置读库，读请求会回退到写库。

---

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `database_driver` | `''` | 数据库驱动名。未设置时自动从 `dsn` 中解析。 |
| `database` | `null` | 单数据库配置。当 `database_list` 为空且 `database_list_try_single` 为 `true` 时，会作为唯一库配置。 |
| `database_list` | `null` | 数据库连接配置列表。`[0]` 为写库，`[1]` 为读库。 |
| `database_list_reload_by_setting` | `true` | 是否允许从 `DuckPhpSettings.config.php` 的 `database_list` 或 `database` 重新加载配置。 |
| `database_list_try_single` | `true` | 当 `database_list` 为空时，是否尝试使用 `database` 作为单库配置。 |
| `database_log_sql_query` | `false` | 是否记录执行的 SQL 语句。 |
| `database_log_sql_level` | `'debug'` | SQL 日志级别。 |
| `database_class` | `''` | 自定义数据库类名。为空时使用 `DuckPhp\Db\Db`。 |

---

## 配置方式

### 通过 `config/DuckPhpSettings.config.php`

```php
<?php
return [
    'database_list' => [
        0 => [
            'dsn' => 'mysql:host=127.0.0.1;dbname=test;port=3306;charset=utf8mb4',
            'username' => 'root',
            'password' => '123456',
            'driver_option' => [],
            'PDO' => 'PDO',
        ],
        1 => [
            'dsn' => 'mysql:host=127.0.0.2;dbname=test;port=3306;charset=utf8mb4',
            'username' => 'root',
            'password' => '123456',
        ],
    ],
];
```

### 通过 `App::$options`

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'database_list' => [
            0 => ['dsn' => 'sqlite:db.sqlite3'],
        ],
    ];
}
```

---

## 读写分离规则

- tag `0`：`TAG_WRITE`，用于写操作。
- tag `1`：`TAG_READ`，用于读操作。
- 如果没有配置 tag `1`，读操作会回退到 tag `0`。

---

## 使用方式

### 在模型或业务层中使用

```php
use DuckPhp\Foundation\ModelHelper;

class UserModel extends ModelHelper
{
    public function getUser($id)
    {
        $sql = 'SELECT * FROM users WHERE id = ?';
        return $this->_DB()->fetch($sql, $id);
    }
}
```

### 直接调用 DbManager

```php
use DuckPhp\Component\DbManager;

$db = DbManager::_()->_Db();           // 默认写库
$dbRead = DbManager::_()->_DbForRead(); // 读库
DbManager::_()->_DbCloseAll();          // 关闭所有连接
```

---

## SQL 分页与计数

```php
use DuckPhp\Component\DbManager;

$sql = 'SELECT * FROM users WHERE status = 1';
$pagedSql = DbManager::_()->_SqlForPager($sql, 1, 10);  // 第 1 页，每页 10 条
$countSql = DbManager::_()->_SqlForCountSimply($sql);    // 生成 COUNT 语句
```

---

## 自定义数据库类

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'database_class' => \MyApp\Db\MyDb::class,
    ];
}
```

`MyDb` 需要兼容 `DuckPhp\Db\Db` 的接口。

---

## 注意事项

1. `DbManager` 是单例组件，通过 `DbManager::_()` 访问当前应用实例。
2. 如果没有配置任何数据库，调用 `_Db()` 会抛出异常。
3. SQLite 的 `dsn` 如果使用的是相对路径，会自动拼接项目运行时目录 `path`。
4. 开启 `database_log_sql_query` 后，SQL 会通过 `Logger::_()->log()` 写入日志。

---

## 全部选项

```php
[
    'database_driver' => '',
    'database' => null,
    'database_list' => null,
    'database_list_reload_by_setting' => true,
    'database_list_try_single' => true,
    'database_log_sql_query' => false,
    'database_log_sql_level' => 'debug',
    'database_class' => '',
]
```

---

## 方法列表

### 公共方法

| 方法 | 说明 |
|---|---|
| `Db($tag = null)` | 静态方法，返回指定 tag 的数据库实例。 |
| `DbForWrite()` | 静态方法，返回写库实例。 |
| `DbForRead()` | 静态方法，返回读库实例。 |
| `DbCloseAll()` | 静态方法，关闭所有数据库连接。 |
| `OnQuery($db, $sql, ...$args)` | 静态方法，SQL 查询前的日志回调。 |
| `_Db($tag = null)` | 返回指定 tag 的数据库实例。 |
| `_DbForWrite()` | 返回写库实例。 |
| `_DbForRead()` | 返回读库实例，未配置读库则回退写库。 |
| `_DbCloseAll()` | 关闭所有数据库连接。 |
| `_OnQuery($db, $sql, ...$args)` | 记录 SQL 日志。 |
| `_SqlForPager($sql, $page_no, $page_size = 10)` | 为 SQL 添加分页子句。 |
| `_SqlForCountSimply($sql)` | 根据 SQL 生成 COUNT 语句。 |
| `getDatabaseConfigList()` | 获取解析后的数据库配置列表。 |
| `getDatabaseDriver()` | 获取当前数据库驱动名。 |
| `setBeforeGetDbHandler($callback)` | 设置获取数据库前的回调。 |

### 受保护方法

| 方法 | 说明 |
|---|---|
| `initOptions(array $options)` | 解析单库与多库配置。 |
| `initContext(object $context)` | 从 setting 中重新加载数据库配置。 |
| `getDatabase($tag)` | 获取或创建指定 tag 的数据库实例。 |
| `createDatabaseObject(array $db_config)` | 根据配置创建数据库对象。 |

---

## 相关链接

- [DuckPhp\Db\Db](Db-Db.md)
- [DuckPhp\Foundation\ModelHelper](Foundation-ModelHelper.md)
- [DuckPhp\Core\App](Core-App.md)
