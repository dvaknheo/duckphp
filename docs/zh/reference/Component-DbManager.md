# DuckPhp\Component\DbManager

数据库连接管理器。

## 简介

`DbManager` 组件负责管理数据库连接的创建、销毁与访问。它支持单个数据库配置和数据库列表配置，并支持读写分离。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `database_driver` | `''` | 数据库驱动。通常从 `dsn` 中自动推断。 |
| `database` | `null` | 单数据库配置。当 `database_list` 未设置且 `database_list_try_single` 为 `true` 时，会自动将其作为唯一数据库。 |
| `database_list` | `null` | 数据库配置列表。索引 `0` 为写库，索引 `1` 为读库。 |
| `database_list_reload_by_setting` | `true` | 是否允许从 `DuckPhpSettings.config.php` 的 `database_list` 或 `database` 重新加载配置。 |
| `database_list_try_single` | `true` | 当 `database_list` 未设置时，是否尝试将 `database` 作为单数据库使用。 |
| `database_log_sql_query` | `false` | 是否记录 SQL 查询日志。 |
| `database_log_sql_level` | `'debug'` | SQL 日志级别。 |
| `database_class` | `''` | 自定义数据库类。为空时使用 `DuckPhp\Db\Db`。 |

## 数据库配置格式

单数据库配置：

```php
class App extends DuckPhp
{
    public $options = [
        'database' => [
            'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4',
            'username' => 'root',
            'password' => 'secret',
        ],
    ];
}
```

多数据库配置（读写分离）：

```php
class App extends DuckPhp
{
    public $options = [
        'database_list' => [
            [ // 索引 0：写库
                'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4',
                'username' => 'write_user',
                'password' => 'write_pass',
            ],
            [ // 索引 1：读库
                'dsn' => 'mysql:host=127.0.0.2;dbname=test;charset=utf8mb4',
                'username' => 'read_user',
                'password' => 'read_pass',
            ],
        ],
    ];
}
```

也支持从 `config/DuckPhpSettings.config.php` 中配置：

```php
<?php
return [
    'database_list' => [
        [
            'dsn' => 'sqlite:' . __DIR__ . '/../runtime/database.db',
        ],
    ],
];
```

## 读写分离规则

| 方法 | 使用的 tag | 说明 |
|---|---|---|
| `Db()` / `Db(null)` | `TAG_WRITE` (0) | 未指定 tag 时，返回写库。 |
| `DbForWrite()` | `TAG_WRITE` (0) | 返回写库。 |
| `DbForRead()` | `TAG_READ` (1) 或 `TAG_WRITE` (0) | 如果配置了读库则返回读库，否则返回写库。 |

常量定义：

```php
const TAG_WRITE = 0;
const TAG_READ = 1;
```

## 使用方式

### 通过 Model Helper

```php
use DuckPhp\Foundation\Model\Helper;

$db = Helper::Db();              // 写库
$db = Helper::DbForWrite();      // 写库
$db = Helper::DbForRead();       // 读库或写库
```

### 通过 DbManager 组件

```php
use DuckPhp\Component\DbManager;

$db = DbManager::Db();             // 写库
$db = DbManager::DbForWrite();    // 写库
$db = DbManager::DbForRead();     // 读库或写库

DbManager::DbCloseAll();          // 关闭所有数据库连接
```

### 直接执行 SQL

```php
use DuckPhp\Foundation\Model\Helper;

$sql = "SELECT * FROM users WHERE id = ?";
$user = Helper::Db()->fetch($sql, $userId);
```

### 分页与计数辅助

```php
use DuckPhp\Component\DbManager;

$sql = "SELECT * FROM posts";
$pageSql = DbManager::_()->_SqlForPager($sql, 2, 10);    // 生成带 LIMIT 的 SQL
countSql  = DbManager::_()->_SqlForCountSimply($sql);   // 生成 COUNT 语句
```

## 自定义数据库类

通过 `database_class` 选项可以替换默认的 `DuckPhp\Db\Db`：

```php
class App extends DuckPhp
{
    public $options = [
        'database_class' => MyDbClass::class,
    ];
}

class MyDbClass extends DuckPhp\Db\Db
{
    // 自定义数据库行为
}
```

## SQL 日志

开启 SQL 查询日志：

```php
class App extends DuckPhp
{
    public $options = [
        'database_log_sql_query' => true,
        'database_log_sql_level' => 'debug',
    ];
}
```

开启后，每次查询会通过 `DuckPhp\Core\Logger` 记录日志。

## 注意事项

1. 未配置 `database_list` 且未配置 `database` 时，调用 `Db()` 会抛出 `ErrorException`。
2. `DbForRead()` 在读库未配置时会自动回退到写库，但 `DbForWrite()` 不会回退到读库。
3. SQLite 数据库文件如果是相对路径，会自动基于 `path_runtime` 目录解析。
4. 关闭所有连接请使用 `DbCloseAll()`，它会调用每个数据库对象的 `close()` 方法。
5. 自定义数据库类需要能被 `new $class()` 实例化，且通常应继承 `DuckPhp\Db\Db`。

## 全部选项

```php
public $options = [
    'database_driver' => '',
    'database' => null,
    'database_list' => null,
    'database_list_reload_by_setting' => true,
    'database_list_try_single' => true,
    'database_log_sql_query' => false,
    'database_log_sql_level' => 'debug',
    'database_class' => '',
];
```

## 方法列表

### 公共方法

    public static function Db($tag = null)
获取指定 tag 的数据库实例。未指定 tag 时返回写库

    public static function DbForWrite()
获取写库实例

    public static function DbForRead()
获取读库实例；如果未配置读库，则返回写库

    public static function DbCloseAll()
关闭所有已创建的数据库连接

    public static function OnQuery($db, $sql, ...$args)
SQL 查询钩子，供 `Db` 对象回调以记录日志

    public function getDatabaseConfigList()
获取最终解析后的数据库配置列表

    public function getDatabaseDriver()
获取数据库驱动类型，从配置中自动推断

    public function setBeforeGetDbHandler($db_before_get_object_handler)
设置获取数据库前的回调函数，签名为 `fn(DbManager $manager, int $tag)`

    public function _Db($tag = null)
实例方法版本，获取指定 tag 的数据库

    public function _DbForWrite()
实例方法版本，获取写库

    public function _DbForRead()
实例方法版本，获取读库或写库

    public function _DbCloseAll()
实例方法版本，关闭所有连接

    public function _OnQuery($db, $sql, ...$args)
实例方法版本，SQL 日志记录

    public function _SqlForPager($sql, $page_no, $page_size = 10)
实例方法版本，生成分页 SQL

    public function _SqlForCountSimply($sql)
实例方法版本，生成 COUNT SQL

### 受保护方法

    protected function initOptions(array $options)
合并 `database` 和 `database_list` 配置

    protected function initContext(object $context)
根据 `DuckPhpSettings.config.php` 重新加载配置

    protected function getDatabase($tag)
根据 tag 创建或返回缓存的数据库实例

    protected function createDatabaseObject($db_config)
使用配置创建数据库对象，处理 SQLite 相对路径

    protected function getRuntimePath()
获取 runtime 目录路径

## 相关链接

- [DuckPhp\Db\Db](Db-Db.md)
- [DuckPhp\Foundation\Model\Helper](Foundation-Model-Helper.md)
- [DuckPhp\Core\Logger](Core-Logger.md)
