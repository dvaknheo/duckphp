# 数据库

## 配置

在 `config/DuckPhpSettings.config.php` 中配置数据库连接：

```php
<?php
return [
    'database_list' => [
        // 写连接（tag 0）
        [
            'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4;',
            'username' => 'root',
            'password' => 'password',
            'driver_options' => [],
        ],
        // 读连接（tag 1），可选
        [
            'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4;',
            'username' => 'root',
            'password' => 'password',
        ],
    ],
];
```

也支持在 `RunQuickly` 时直接传入：

```php
\MyApp::RunQuickly([
    'database_list' => [
        ['dsn' => 'sqlite:' . __DIR__ . '/runtime/db.sqlite'],
    ],
]);
```

## 支持的数据库

通过 PDO 驱动，支持所有 PDO 兼容的数据库：

| 驱动 | DSN 示例 |
|---|---|
| MySQL | `mysql:host=127.0.0.1;dbname=test;charset=utf8mb4;` |
| SQLite | `sqlite:/path/to/db.sqlite` |
| PostgreSQL | `pgsql:host=localhost;dbname=test` |
| SQL Server | `sqlsrv:Server=localhost;Database=test` |

SQLite 相对路径根目录为 `runtime/`。

## 用法

### 在 Controller 中使用

```php
use DuckPhp\Foundation\Controller\Helper;

// 获取读连接
$rows = Helper::DbForRead()->fetchAll("SELECT * FROM users WHERE status=?", 1);

// 获取写连接
Helper::DbForWrite()->execute("UPDATE users SET name=? WHERE id=?", 'new_name', 1);

// 指定数据库 tag
$db = Helper::Db(0);  // 等同于 DbForWrite
$db = Helper::Db(1);  // 等同于 DbForRead
```

### 在 Model 中使用

继承 `Foundation\Model\Base` 后自动获得 `DbForRead`/`DbForWrite`：

```php
use DuckPhp\Foundation\Model\Helper;

Helper::DbForRead()->fetchAll(...);
Helper::DbForWrite()->execute(...);
```

### 直接在 Business 中使用

```php
use DuckPhp\Component\DbManager;

$rows = DbManager::_()->_DbForRead()->fetchAll("SELECT * FROM users");
```

## Db 类 API

底层 `DuckPhp\Db\Db` 类封装了 PDO：

```php
$db = Helper::DbForRead();

$db->fetch($sql, ...$args);        // 获取单行（关联数组）
$db->fetchAll($sql, ...$args);     // 获取多行
$db->fetchColumn($sql, ...$args);  // 获取单列值
$db->fetchObject($sql, ...$args);  // 获取单行对象

$db->execute($sql, ...$args);      // 执行 SQL，返回 bool（是否成功）

$db->quote($value);                // 安全引号
$db->insertData($table, $data);    // 插入数据（关联数组）
$db->updateData($table, $id, $data, $pk); // 更新数据
```

> **注意**：`execute()` 当前实现返回的是 `bool`，并非影响行数。若需准确判断影响行数，建议先 `SELECT` 确认或使用 PDOStatement 自行获取。`rowCount()` 在部分场景下也不能直接反映实际影响行数。

## 读写分离

- 如果只配置了一个数据库连接，读写操作都使用它
- 如果配置了两个连接，`_DbForRead()` 使用 tag 1，`_DbForWrite()` 使用 tag 0
- `fetch*` 系列方法使用读连接，`execute` 使用写连接

## SQL 日志

启用 SQL 日志（调试用）：

```php
$options = [
    'database_log_sql_query' => true,
    'database_log_sql_level' => 'debug',
];
```

## 自定义数据库类

可以通过 `database_class` 选项替换默认的 `Db` 类：

```php
$options = [
    'database_class' => \MyProject\MyDb::class,
];
```

## 清理数据库连接

```php
Helper::DbCloseAll();   // 关闭所有数据库连接
// 或
DbManager::_()->_DbCloseAll();
```
