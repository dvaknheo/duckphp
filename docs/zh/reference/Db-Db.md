# DuckPhp\Db\Db

数据库操作类。

## 简介

`Db` 是 DuckPHP 默认的数据库操作类，基于 PDO 封装。它实现了 `DbInterface` 接口，并混入 `DbAdvanceTrait` 提供额外的辅助方法。通常通过 `DbManager` 获取 `Db` 实例，而不是直接实例化。

## 配置格式

`Db` 通过 `init()` 接收配置数组，典型配置如下：

```php
[
    'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4',
    'username' => 'root',
    'password' => 'secret',
    'driver_options' => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ],
]
```

## 使用方式

### 通过 Model Helper 获取

```php
use DuckPhp\Foundation\Model\Helper;

$db = Helper::Db();
```

### 通过 DbManager 获取

```php
use DuckPhp\Component\DbManager;

$db = DbManager::Db();
```

### 查询数据

```php
$users = $db->fetchAll("SELECT * FROM users WHERE status = ?", 1);
$user = $db->fetch("SELECT * FROM users WHERE id = ?", $id);
$count = $db->fetchColumn("SELECT COUNT(*) FROM users");
```

### 执行写操作

```php
$ret = $db->execute("UPDATE users SET name = ? WHERE id = ?", $name, $id);
$affected = $db->rowCount();
```

### 使用命名参数

```php
$sql = "SELECT * FROM users WHERE status = :status AND role = :role";
$users = $db->fetchAll($sql, ['status' => 1, 'role' => 'admin']);
```

### 使用表名宏

```php
$db->table('users');
$users = $db->fetchAll("SELECT * FROM `'TABLE'` WHERE status = ?", 1);
```

### 获取原始 PDO

```php
$pdo = $db->PDO();
```

### 设置结果类

```php
$db->setObjectResultClass(User::class);
$user = $db->fetchObject("SELECT * FROM users WHERE id = ?", $id);
```

## 注意事项

1. 默认驱动选项设置了 `PDO::ERRMODE_EXCEPTION` 和 `PDO::FETCH_ASSOC`。
2. `quote()` 支持数组递归转义。
3. `execute()` 返回的是执行是否成功，`rowCount()` 返回影响行数。
4. 查询钩子 `beforeQueryHandler` 可用于 SQL 日志记录。
5. 通过 `database_class` 选项可以替换默认的 `Db` 类。

## 方法列表

### 公共方法

    public function init($options = [], $context = null)
初始化数据库连接配置

    public function close(): void
关闭 PDO 连接

    public function PDO($pdo = null)
获取或设置 PDO 实例

    public function setBeforeQueryHandler($handler)
设置查询前回调，签名为 `fn(Db $db, string $sql, ...$args)`

    public function quote($string)
转义字符串或数组

    public function qouteScheme($name)
根据驱动类型返回标识符引用（如 MySQL 的反引号）

    public function buildQueryString($sql, ...$args)
构建可直接执行的 SQL 字符串（用于调试，不建议直接执行）

    public function table($table_name)
设置当前表名宏

    public function doTableNameMacro($sql)
将 SQL 中的 `'TABLE'` 替换为当前表名

    public function setObjectResultClass($resultClass)
设置 `fetchObject` 返回的对象类

    public function fetchAll($sql, ...$args)
执行查询并返回所有行

    public function fetch($sql, ...$args)
执行查询并返回第一行

    public function fetchColumn($sql, ...$args)
执行查询并返回第一列

    public function fetchObject($sql, ...$args)
执行查询并返回单个对象

    public function fetchObjectAll($sql, ...$args)
执行查询并返回对象数组

    public function execute($sql, ...$args)
执行写操作 SQL

    public function rowCount(): int
返回最近一次写操作影响的行数

    public function lastInsertId()
返回最后一次插入的自增 ID

### 受保护方法

    protected function check_connect(): void
检查并创建 PDO 连接

    protected function exec(string $sql, ...$args): \PDOStatement
执行 SQL 并返回 PDOStatement

## 相关链接

- [DuckPhp\Db\DbInterface](Db-DbInterface.md)
- [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md)
- [DuckPhp\Component\DbManager](Component-DbManager.md)
