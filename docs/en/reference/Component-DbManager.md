# DuckPhp\Component\DbManager

Database manager: manages a set of DB connection configs, supports "write (tag0) / read (tag1)" or index-based connection picking, builds connections lazily and closes them on demand; optional SQL logging.

## Introduction

`DbManager extends ComponentBase` is DuckPHP's single entry point for getting database handles:

- gets `database_list` (a `[ [dsn…], … ]` array) or a single `database` from `options`;
- config can come from options or (if reload_by_setting is on) `database_list/database` in setting;
- `tag` 0=write (`TAG_WRITE`), 1=read (`TAG_READ`); without a separate `_DbForRead` config, reads fall back to write;
- a connection is only instantiated the first time it is requested (into `DuckPhp\Db\Db` or your `database_class`), then cached;
- SQL query logging is optional (`OnQuery`).

The manager is only a connection factory/cache; it holds no business logic. Business-layer helpers such as `Db()` read from here.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class DbManager extends ComponentBase`
- Constants: `TAG_WRITE=0`, `TAG_READ=1`.
- Cache target class: `DuckPhp\Db\Db` by default (changeable via options).

## Options

`DbManager::$options`:

| Option | Default | Description |
|---|---|---|
| `database_driver` | `''` | Driver result (derived during init/from setting and written back). |
| `database` | `null` | Convenience single connection config (dsn/…). |
| `database_list` | `null` | Connection config list (array). This item wins when given. |
| `database_list_reload_by_setting` | `true` | When not explicitly configured (no database given), allows taking database_list from setting. |
| `database_list_try_single` | `true` | When database_list is empty, falls back to wrapping the single `database`/`setting.database` into an array. |
| `database_log_sql_query` | `false` | Whether to log every SQL statement. |
| `database_log_sql_level` | `'debug'` | Log level for the SQL log above. |
| `database_class` | `''` | Custom connection class; empty means `Db`. |

## Usage

```php
use DuckPhp\Component\DbManager;
// routing; tag0 defaults to the write connection
$db = DbManager::_()->_Db();            // same tag
DbManager::_()->_DbForWrite();
DbManager::_()->_DbForRead();           // read tag; falls back to write when unconfigured
```

Database initialization, assembling config:

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

Close all:

```php
DbManager::_()->_DbCloseAll();
```

## Caveats

1. `dsn = driver:…`; a relative `sqlite:` path is made absolute against `App::Root()->options['path']`.
2. After init, the derived driver is written back into options (for upper layers).
3. An out-of-range tag array index throws `ErrorException` (missing database_list[$tag]).
4. Get the connection object right before the query; the caller is responsible for releasing it; closing is best done uniformly at the end of the request via `_DbCloseAll` or in a handler.
5. Query logging: hooks into `DbManager::_OnQuery` via the connection object's `setBeforeQueryHandler`.

## Methods

### Public methods

    public function init(array $options, ?object $context = null)
Parent component init + writes back `database_driver = getDatabaseDriver()`.

    public function getDatabaseConfigList(): array
The current (final) connection config list.

    public function getDatabaseDriver(): string
The options config wins; otherwise infers the driver from the first non-empty dsn and writes it back.

    public static function Db($tag = null)
Static connection getter: `null` → write (tag 0); with a tag, takes that tag.

    public static function DbForWrite()
Takes the tag 0 write connection.

    public static function DbForRead()
Takes the read connection when a read config exists, otherwise falls back to write.

    public static function DbCloseAll()
Iterates the cached dbs, `close()`es each, then clears.

    public static function OnQuery($db, $sql, ...$args)
SQL log hook: when enabled, `Logger->log(level, '[sql]:…')`.

    public function setBeforeGetDbHandler($db_before_get_object_handler)
Sets a custom hook that runs "before every connection fetch".

    public function _Db($tag = null)
Instance-side entry: write by default; throws a missing exception on error.

    public function _DbForWrite()
Instance implementation of `DbForWrite()`: takes the tag 0 write connection.

    public function _DbForRead()
Instance implementation of `DbForRead()`: takes the read connection when a read config exists, otherwise falls back to write.

    public function _DbCloseAll()
Instance implementation of `DbCloseAll()`: closes and clears cached connections.

    public function _OnQuery($db, $sql, ...$args)
Instance implementation of `OnQuery()`.

    public function _SqlForPager($sql, $page_no, $page_size = 10)
Hands pagination SQL generation to the write connection.

    public function _SqlForCountSimply($sql)
Hands a simple count to the connection.

### Protected methods

    protected function initOptions(array $options): void
Assembles database_config_list: from `database_list` or (try single) from `database`.

    protected function initContext(object $context): void
When `reload_by_setting`, takes database_list/database via `context->_Setting` to override the config list.

    protected function getDatabase($tag): object
Lazily builds a connection: calls the beforeGet handler first if present; otherwise builds a Db from config and caches it.

    protected function createDatabaseObject(array $db_config): object
Builds the connection object: absolutizes relative sqlite paths; uses `database_class` or `DuckPhp\Db\Db`; inits config; hooks `setBeforeQueryHandler` when SQL logging is on.


## Related links

- [DuckPhp\Db\Db](Db-Db.md) — the default connection implementation
- [DuckPhp\Core\Logger](Core-Logger.md)
- The model layer's Db()/DbForRead etc. (Foundation/ModelTrait)
- component: DbManager is also one of the components loaded by the DuckPhp root (see Core)
