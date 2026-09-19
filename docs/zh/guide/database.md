# 2-5 数据库

> 解决什么问题：怎么配数据库连接、多库与读写分离怎么用、事务怎么写、分页与 SQL 导出怎么做、SQL 里的表名前缀宏是什么。
> 前置：[第 1-5 章 配置与设置](configuration.md)、[第 2-1 章 四层架构与调用规范](layers.md)。预计 20 分钟。
> 示例：`demo/public/dbtest.php`（真跑得起来的 SQLite 增删改查 + 分页，244 行，数据库文件 `demo/runtime/dbtest.sqlite`）。

```bash
php -S 127.0.0.1:8080 -t demo/public
# 直接访问 http://127.0.0.1:8080/dbtest.php （文件末尾那句「没被挂载就自己跑」会启动独立入口）
# 它同时也被挂成 demo 的子应用：http://127.0.0.1:8080/demo.php?_r=db_test/
```

## 最小示例

`demo/public/dbtest.php` 的配置与查询（真实文件，节选）：

```php
class DbTestApp extends DuckPhp
{
    public $options = [
        'local_database' => true,          // 这个应用单独用一套数据库连接
        'database' => [
            'dsn' => 'sqlite:runtime/dbtest.sqlite',
            'username' => null,
            'password' => null,
            'driver_options' => [],
        ],
    ];
}
```

```php
class TestModel
{
    use ModelTrait;
    public function __construct()
    {
        $this->table_name = 'test';
    }
    public function getDataList($page, $pagesize)
    {
        $sql = "select * from `'TABLE'` order by id desc";       // `'TABLE'` 是表名宏
        $total = $this->fetchColumn(Helper::SqlForCountSimply($sql));
        $list  = $this->fetchAll(Helper::SqlForPager($sql, $page, $pagesize));
        return [$total, $list];
    }
    public function addData($data)
    {
        $sql = "insert into `'TABLE'` (content) values(?)";
        $this->execute($sql, $data['content']);
        return Helper::Db()->lastInsertId();
    }
}
```

三个要点：连接用 `database` 选项配、SQL 里用 `` `'TABLE'` `` 宏代替真表名、读写走 `fetch*` / `execute`（模型层的封装见[第 2-6 章](model.md)）。

## 机制说明

### 1. 连接配置：`DbManager` 与 `Db`

| 选项 | 默认 | 说明 |
|---|---|---|
| `database` | `null` | **单条**连接配置：`['dsn'=>…, 'username'=>…, 'password'=>…, 'driver_options'=>…]` |
| `database_list` | `null` | **多条**连接配置（读写分离/多库用） |
| `database_list_try_single` | `true` | 只给了 `database` 时，自动当成一条 |
| `database_list_reload_by_setting` | `true` | 允许用 `Setting()` 里的 `database_list`/`database` 覆盖选项 |
| `database_driver` | `''` | 驱动名（`sqlite`/`mysql`/`pgsql`），一般由 DSN 推断 |
| `database_class` | `''` | 换掉 [`Db`](../reference/Db-Db.md) 实现类 |
| `database_log_sql_query` | `false` | 是否把 SQL 写日志 |
| `database_log_sql_level` | `'debug'` | 上面那条日志的级别 |
| `local_database` | `false` | 子应用是否**独占**一套连接（[第 3-4 章](component-sharing.md)） |

[`DbManager`](../reference/Component-DbManager.md) 是连接管理器（`TAG_WRITE = 0` 写、`TAG_READ = 1` 读），`Db` 是执行器：

```php
Helper::Db();               // 写连接
Helper::DbForRead();        // 读连接
Helper::Db(1);              // 按 tag 取（1 = TAG_READ）
Helper::Db()->PDO();        // 原生 PDO（复杂 SQL 用）
Helper::DatabaseDriver();   // 当前驱动名
```

**连接是惰性的**：第一次真正执行 SQL 时才建立 PDO。

### 2. 读写分离

`database_list` 里给多条配置即可，`DbManager` 按 tag 分配：

```php
$options = [
    'database_list' => [
        ['dsn' => 'mysql:host=master;dbname=app', 'username' => 'u', 'password' => 'p'],  // tag 0 = 写
        ['dsn' => 'mysql:host=slave;dbname=app',  'username' => 'u', 'password' => 'p'],  // tag 1 = 读
    ],
];
```

约定：**读走读连接、写走写连接**——模型层的 `fetch*` / `execute` 已经这么分流（[第 2-6 章](model.md)）。

### 3. 执行 SQL：`Db` 的方法

| 方法 | 用途 |
|---|---|
| `fetchAll($sql, ...$args)` | 多行（数组的数组） |
| `fetch($sql, ...$args)` | 单行 |
| `fetchColumn($sql, ...$args)` | 单值（常用于 `count(*)`） |
| `fetchObject()` / `fetchObjectAll()` | 返回对象（结果类可 `setObjectResultClass()` 指定） |
| `execute($sql, ...$args)` | 执行写操作；**成功返回受影响行数、失败返回 0** |
| `rowCount()` / `lastInsertId()` | 上一次执行/插入的信息 |
| `quote($v)` / `quoteScheme($name)` | 转义值 / 转义标识符（表名、字段名） |
| `table($name)` | 绑定表名（配合下面的宏） |
| `PDO()` | 原生 PDO |

参数一律用占位符，**永远不要把用户输入拼进 SQL**：

```php
$rows = Helper::Db()->fetchAll('select * from note where status=? and id>?', 'on', 10); // ✅
$rows = Helper::Db()->fetchAll("select * from note where title='{$title}'");           // ❌ 注入
```

### 4. 表名前缀与 `` `'TABLE'` `` 宏

配了 `table_prefix` 之后，SQL 里不用手写前缀——写 `` `'TABLE'` `` 宏，执行前替换成「前缀 + 当前模型的表名」：

```php
$sql = "select * from `'TABLE'` where id=?";
// table_prefix='app_'、模型表名 note → select * from `app_note` where id=?
```

宏的字面量是 `` `'TABLE'` ``（含反引号），[`ModelTrait::prepare()`](../reference/Foundation-ModelTrait.md) 负责替换（[第 2-6 章](model.md)），底层是 `Db::doTableNameMacro()`。

### 5. 增删改的便捷方法

`Db` 上有一组「按数组拼 SQL」的工具（[`DbAdvanceTrait`](../reference/Db-DbAdvanceTrait.md)），适合写通用模型：

```php
$db = Helper::Db();
$db->findData('note', 42);                        // 按主键查一行
$db->insertData('note', ['title' => 't']);        // 插入（返回自增 id）
$db->updateData('note', 42, ['title' => 't2']);   // 按主键更新
$db->deleteData('note', 42);                      // 软删（默认写 is_deleted）

$db->quoteAndArray(['status' => 'on']);           // WHERE 片段（AND 连接）
$db->quoteIn([1, 2, 3]);                          // IN (...) 片段
$db->quoteSetArray(['title' => 'x']);             // SET 片段
$db->quoteInsertArray(['title' => 'x']);          // INSERT 的字段/值片段
```

值都经 `quote()` 转义；但**表名/字段名只能来自代码、不能来自用户输入**这条底线仍要守住。

### 6. 事务

框架没有额外包一层事务 API，直接用 PDO（官方示例也是这么做的）：

```php
$pdo = Helper::Db()->PDO();
try {
    $pdo->beginTransaction();
    NoteModel::_()->add(['title' => 'a']);
    NoteModel::_()->add(['title' => 'b']);
    $pdo->commit();
} catch (\Throwable $ex) {
    $pdo->rollBack();
    throw $ex;                 // 交给异常机制（第 2-11 章）
}
```

事务要**在同一个连接**上开：读写分离时别在读连接上开事务却去写主库。

### 7. 分页

分页由 [`Pager`](../reference/Component-Pager.md) 组件 + SQL 工具配合（`demo/public/dbtest.php` 是完整例子）：

```php
$sql   = "select * from `'TABLE'` order by id desc";
$total = $this->fetchColumn(Helper::SqlForCountSimply($sql));                       // 生成 count SQL
$list  = $this->fetchAll(Helper::SqlForPager($sql, Helper::PageNo(), Helper::PageWindow(3)));
$pager = Helper::PageHtml($total);                                                  // 分页条 HTML
```

`Pager` 选项：`url`（分页链接基址，默认当前路径）、`current`（当前页，默认取自 `Helper::PageNo()`）、`page_size`（默认 30）、`page_key`（URL 参数名，默认 `page`）、`rewrite`（重写规则）。

### 8. SQL 导出

需要把表结构/数据导出成 SQL（安装器、备份）时用 [`Ext\SqlDumper`](../reference/Ext-SqlDumper.md)，驱动细节由 [`SqlDumperSupporter*`](../reference/Ext-SqlDumperSupporter.md) 提供：

| 类 | 支持 |
|---|---|
| `Ext\SqlDumper` | 通用导出器（表前缀被写成 `{prefix}` 占位） |
| [`Ext\SqlDumperSupporterByMysql`](../reference/Ext-SqlDumperSupporterByMysql.md) / `ByPgsql` / `BySqlite` | 各驱动方言 |

导出的 SQL 里用 `{prefix}` 表示表前缀，Web 安装流程（[第 3-6 章](installer.md)）执行时会换成实际前缀。

## 常见写法

**① 多库：`database_list` 给多条**

```php
$logDb = Helper::Db(1);            // tag 1 = 第二个库（示例里当读库用，多库时同理由 tag 区分）
```

**② 复杂查询直接写 SQL**

```php
$sql = 'select n.*, u.name from note n left join user u on u.id=n.user_id where n.status=?';
$rows = Helper::DbForRead()->fetchAll($sql, 'on');
```

**③ SQL 写日志（排查慢查询/奇怪行为）**

```php
$options = ['database_log_sql_query' => true, 'database_log_sql_level' => 'debug'];
```

**④ 生产/开发用不同库：靠 Setting 覆盖**

`database_list_reload_by_setting` 默认为真，所以密码、DSN 这类环境相关项可以放进 `DuckPhpSettings.config.php` 或 `.env`（[第 1-5 章](configuration.md)）。

**⑤ 建表/初始化数据写成模型的 `install()`**

`demo/public/dbtest.php` 的 `TestModel::init()` 就是「`CREATE TABLE IF NOT EXISTS` + 宏」，由控制器构造时调一次。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `no such table` | DSN 指到别的库，或表还没建 | 检查 `database.dsn` 与初始化 SQL；SQLite 相对 DSN 需要可写目录 |
| 表名带前缀却查不到表 | SQL 里手写死了表名，或宏写法不对 | 用 `` `'TABLE'` ``（带反引号）；确认 `table_prefix` |
| `execute()` 返回 `0`，以为「没执行」 | 语义是**成功返回受影响行数、失败 0** | 结合异常判断；`UPDATE` 无字段变化也会是 0 |
| 读库里查不到刚写入的数据 | 读写分离的复制延迟 | 写后立即读的场景显式用 `Helper::Db()`（写连接） |
| SQLite 报 `unable to open database file` | 目录不可写（`runtime/` 权限） | 修目录权限（[第 1-7 章](deployment.md)） |
| 事务没生效 | 事务与写操作不在同一连接 | 同一连接上完成；读写分离时特别注意 |
| 用户输入直接进了 SQL | 拼串 | 一律占位符；标识符用 `quoteScheme()` |
| 分页总数不对 | 计数时用了带 `limit` 的 SQL | 用 `Helper::SqlForCountSimply($sql)` 生成计数 SQL |

## 下一步

- [第 2-6 章 模型层](model.md)：把这一章的能力封装成模型，业务层只看模型。
- [第 2-8 章 表单与数据验证](validator.md)：数据入库前的校验。
- [第 3-6 章 安装器与 Web 安装流程](installer.md)：`SqlDumper` 与 `{prefix}` 的实际用法。
- 参考手册：[DuckPhp\Db\Db](../reference/Db-Db.md)、[DuckPhp\Db\DbAdvanceTrait](../reference/Db-DbAdvanceTrait.md)、[DuckPhp\Component\DbManager](../reference/Component-DbManager.md)、[DuckPhp\Component\Pager](../reference/Component-Pager.md)、[DuckPhp\Ext\SqlDumper](../reference/Ext-SqlDumper.md)。
