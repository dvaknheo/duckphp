# DuckPhp\Db\Db

## 简介

`Db` 是 DuckPHP 默认的数据库连接对象，实现 `DbInterface` 并组合 `DbAdvanceTrait`。它直接封装一个 PDO 实例（属性 `$pdo`），对外提供：

- 连接管理：`init()`/`check_connect()`/`close()`/`PDO()`；
- 安全引用与拼 SQL 辅助：`quote()`/`quoteScheme()`/`buildQueryString()`/`doTableNameMacro()`；
- 查询：`fetch*` 系列（关联数组 / 单列 / 对象）；
- 执行与结果：`execute()`/`rowCount()`/`lastInsertId()`；
- 查询前钩子：`setBeforeQueryHandler()`；
- 对象结果类 / 表名快捷设置：`setObjectResultClass()`/`table()`。

该类**不继承 `ComponentBase`**（不依赖 Phase 容器），实例通常由 `Component\DbManager` 创建并注入配置（`dsn`/`username`/`password`/`driver_options`），业务层经 `Helper::Db()` 等拿到后直接调用。

## 类信息

- 命名空间：`DuckPhp\Db`
- 声明：`class Db implements DbInterface`
- 使用的 Trait：`DuckPhp\Db\DbAdvanceTrait`（其方法见 [Db-DbAdvanceTrait](Db-DbAdvanceTrait.md)，本页不重复）

## 配置

`Db` 没有 `ComponentBase` 式 `$options` 属性；`init(array $options)` 直接把传入数组存为 `public $config`，连接时使用以下键：

| 键 | 默认值 | 说明 |
|---|---|---|
| `dsn` | 无（必须） | PDO DSN，例如 `mysql:host=127.0.0.1;dbname=test`；连接后按 `:` 前段识别 `driver`（`sqlite`/`mysql`/`pgsql`…）。 |
| `username` | `null` | 数据库用户名。 |
| `password` | `null` | 数据库密码。 |
| `driver_options` | `[]` | 附加 PDO 选项，会与默认选项深度合并（默认：`\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION`、`\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC`）。 |

## 使用方式

```php
use DuckPhp\Db\Db;

$db = (new Db())->init([
    'dsn'      => 'mysql:host=127.0.0.1;dbname=test',
    'username' => 'root',
    'password' => 'secret',
]);

$rows = $db->fetchAll('select * from user where age > ?', 18);
$one  = $db->fetch('select * from user where id = :id', ['id' => 3]);
$name = $db->fetchColumn('select name from user where id = ?', 3);
```

### 表名宏与链式设置

```php
$db->table('user');                       // 之后 SQL 里的 `'TABLE'` 会被替换成 `user`
$db->fetchAll("select * from `'TABLE'` where status = 1");
// 实际执行：select * from `user` where status = 1

$db->setObjectResultClass(MyUser::class); // fetchObject(All) 的结果类
```

### 查询前钩子

```php
$db->setBeforeQueryHandler(function ($db, $sql, ...$args) {
    // 每次 prepare 前回调，可做慢查询日志等
});
```

### 引用与拼 SQL

```php
$idList = $db->quote([1, 2, 3]);          // 数组逐项引用 → "'1','2','3'"
$frag   = $db->buildQueryString(
    'select * from t where a = :a and b = :b',
    ['a' => 1, 'b' => 'x']                // 单数组参数：替换 :key
);
```

## 注意事项

- `quote()` 对数组会递归逐项引用并返回逗号串；对非字符串非数组值原样返回。
- `buildQueryString()`：单个数组参数时把 `:key` 占位符替换为引用后的值；多参数时依次替换 SQL 中的 `?`；无参数时原样返回 SQL。
- `quoteScheme()` 依 `driver` 加标识符外壳：`sqlite` 用双引号、`mysql` 用反引号、`pgsql` 用单引号、其它原样。
- `exec()` 为内部统一执行点：先触发 `beforeQueryHandler`，再做表名宏替换、`prepare` + `execute`，并记录 `$this->success`。
- 查看 `execute()` 时注意：它返回的是**是否成功**（`$this->success`），而 `rowCount()` 给的是**受影响行数**——成功时取 `$sth->rowCount()`，失败时归 0（失败不去碰 statement）。
- `fetchAll/fetch/fetchColumn` 用 `\PDO::FETCH_ASSOC`；`fetchObject/fetchObjectAll` 使用 `setObjectResultClass()` 设置的结果类（默认 `\stdClass`）。

## 方法列表

### 公共方法

    public function init($options = [], ?object $context = null)
接收配置数组存入 `$config` 并触发 `check_connect()` 建立连接（不继承组件基类流程；`$context` 参数被忽略）。

    public function close(): void
关闭连接：清空行数并把 `$pdo` 置 `null`。

    public function PDO($pdo = null)
`$pdo` 非空时替换内部 PDO；随后确保连接并返回当前 `$this->pdo`。

    public function setBeforeQueryHandler($handler)
设置查询前回调：`($handler)($this, $sql, ...$args)`，每次 `exec()` 准备 SQL 前触发。

    public function quote($string)
安全引用：数组逐元素递归引用后用 `,` 连接；字符串走 `$pdo->quote()`；其它类型原样返回。

    public function quoteScheme($name)
按驱动给标识符加外壳：`sqlite` 双引号、`mysql` 反引号、`pgsql` 单引号、默认原样。

    public function buildQueryString($sql, ...$args)
按参数把 SQL 占位符换成引用后的值：单数组参数替换 `:key`，多参数替换 `?`，无参数原样返回。

    public function table($table_name)
记录当前表名（供 `` `'TABLE'` `` 宏替换），返回自身以链式调用。

    public function doTableNameMacro($sql)
若已设置表名，把 SQL 中的 `` `'TABLE'` `` 替换为 `` `表名` ``。

    public function setObjectResultClass($resultClass)
设置 `fetchObject`/`fetchObjectAll` 的结果类，返回自身。

    public function fetchAll($sql, ...$args)
执行并返回全部行（`\PDO::FETCH_ASSOC`）。

    public function fetch($sql, ...$args)
执行并返回一行（`\PDO::FETCH_ASSOC`）。

    public function fetchColumn($sql, ...$args)
执行并返回第一行第一列。

    public function fetchObject($sql, ...$args)
执行并返回一个 `$resultClass` 对象（默认 `\stdClass`）。

    public function fetchObjectAll($sql, ...$args)
执行并返回 `$resultClass` 对象数组（`\PDO::FETCH_CLASS`）。

    public function execute($sql, ...$args)
执行 SQL 并返回是否成功（`$this->success`）；内部同时记录 `rowCount`（成功=受影响行数，失败=0）。

    public function rowCount(): int
返回最近一次执行记录的受影响行数。

    public function lastInsertId()
返回最近一次插入的自增 ID（透传 PDO）。

### 受保护方法

    protected function check_connect(): void
若 `$pdo` 为空则用 `$config` 创建 PDO：合并 `driver_options`、按 `:` 识别 `driver`。

    protected function exec(string $sql, ...$args): \PDOStatement
统一执行点：触发查询前钩子 → 整形参数（单数组解包）→ 表名宏替换 → `prepare` + `execute` → 记录 `$this->success` 并返回 statement。

## 相关链接

- [DuckPhp\Db\DbInterface](Db-DbInterface.md) — 本类实现的契约接口
- [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md) — findData/insertData/quoteIn 等便捷方法
- [DuckPhp\Component\DbManager](Component-DbManager.md) — 数据库管理组件（负责创建与注入本类）
