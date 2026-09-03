# DuckPhp\Component\DbManager

数据库管理器：管理一组 DB 连接配置，支持“写（tag0）/读（tag1）”或按索引取连接，延迟建链并按需关闭；可选记录 SQL 日志。

## 简介

`DbManager extends ComponentBase` 是 DuckPHP 拿数据库句柄的统一入口：

- 通过 `options` 得到 `database_list`（一个 `[ [dsn…], … ]` 数组）或单个 `database`；
- 配置可来自 options 或（若开 reload_by_setting）setting 里的 `database_list/database`；
- `tag` 0=写（`TAG_WRITE`），1=读（`TAG_READ`）；没有 `_DbForRead` 单独配置时读也回落写；
- 只有第一次取某连接才实例化（到 `DuckPhp\Db\Db` 或你给的 `database_class`），其后 cache；
- SQL 查询日志可选（`OnQuery`）。

管理器只是连接工厂/缓存，不写业务。业务层 helpers 如 `Db()` 取这里。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class DbManager extends ComponentBase`
- 常量：`TAG_WRITE=0`、`TAG_READ=1`。
- 缓存目标类：默认 `DuckPhp\Db\Db`（可用 options 改）。

## 选项

`DbManager::$options`：

| 选项 | 默认值 | 说明 |
|---|---|---|
| `database_driver` | `''` | 驱动返回（init/setting 推导并回填）。 |
| `database` | `null` | 单个连接配置（dsn/…）便捷。 |
| `database_list` | `null` | 连接配置列表(数组)。给此项优先。 |
| `database_list_reload_by_setting` | `true` | 未显式 configuration 时（不提供 database）允许从 setting 取 database_list。 |
| `database_list_try_single` | `true` | database_list 空时可回退 单 `database`/`setting.database` 包数组。 |
| `database_log_sql_query` | `false` | 是否对每条 SQL 写日志。 |
| `database_log_sql_level` | `'debug'` | 上面 SQL 日志级别。 |
| `database_class` | `''` | 自定义连接类空则 `Db`。 |

## 使用方式

```php
use DuckPhp\Component\DbManager;
// routes，tag0 缺省写库
$db = DbManager::_()->_Db();            // 同一个 tag
DbManager::_()->_DbForWrite();
DbManager::_()->_DbForRead();           // read tap;无配置回落 write
```

数据库初始化 组装 config：

```php
$dm = DbManager::_()->init([
  'database_list' => [
     ['dsn'=>'mysql:host=…;dbname=…', 'username'=>'…','password'=>'…'],
     ['dsn'=>'mysql:…(slave)', …],
  ],
]);
$r = $dm->_DbForRead();
$w = $dm->_DbForWrite();
```

关闭所有：

```php
DbManager::_()->_DbCloseAll();
```

## 注意事项

1. `dsn = driver:…`，`sqlite:相对` 会被拼到 `App::Root()->options['path']`转绝对。
2. init 后会把推导出的 driver 回填进 options（供上层）。
3. tag 数组下越界会抛 `ErrorException`（缺少 database_list[$tag]）。
4. 连接对象要拿到 query 前调用、由外层负责释放；关闭建议收到请求末尾统一 `_DbCloseAll` 或在 handler 关闭。
5. 查询日志：经连接对象 `setBeforeQueryHandler` 挂钩到 `DbManager::_OnQuery`。

## 方法列表

### 公共方法（连接获取）

    public function init(array $options, ?object $context = null)
父 init + 回填 database_driver=getDatabaseDriver。

    public function getDatabaseConfigList(): array
当前（最终）连接配置列表。

    public function getDatabaseDriver(): string
options 配置优先；否则从第一条非空 dsn 推断并回填 driver。

    public static function Db($tag = null)
静态取句：null→写(tag0)；有 tag 取该 tag。

    public static function DbForWrite() / _DbForWrite()
取 tag0 写连接。

    public static function DbForRead() / _DbForRead()
有 read 配置取读连接，否则回落写。

### 连接创建与缓存

    protected function initOptions(array $options): void
组装 database_config_list：`database_list` 或（try single）按 `database`。

    protected function initContext(object $context): void
reload_by_setting 时用 context->_Setting 取 database_list/database 覆盖 config list。

    protected function getDatabase($tag): object
懒建：若有 beforeGet handler 先调用；不存在则从 config 建Db + cache。

    protected function createDatabaseObject(array $db_config): object
sqlite 相对处理绝对化；用 database_class 或 DuckPhp\Db\Db；init config；若要 SQL-queries log，则 setBeforeQueryHandler。

    public function _Db($tag = null)
实现入口：默认写；错误时抛 missing error。

### 静态壳 & 日志/收尾

    public static function DbCloseAll() / _DbCloseAll()
遍历 cache 内的 db->close() 后清空。

    public static function OnQuery($db, $sql, ...$args) / 实例 _OnQuery(...)
若 SQL 日志开启则 `Logger->log(level,'[sql]:…')`。

    public function setBeforeGetDbHandler($handler)
在每次取连接前可调的自定义钩子。

    public function _SqlForPager($sql, $page_no, $page_size = 10)
把分页 SQL 交给写连接生成器。

    public function _SqlForCountSimply($sql)
交给连接简算 count。

## 相关链接

- [DuckPhp\Db\Db](Db-Db.md) —— 默认连接实现
- [DuckPhp\Core\Logger](Core-Logger.md)
- Model 层的 Db()/DbForRead 等（Foundation/ModelTrait）
- component: DbManager 也是 DuckPhp 根加载的组件之一（见 Core)
