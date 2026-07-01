# Database

## Configuration

Configure database connections in `config/DuckPhpSettings.config.php`:

```php
<?php
return [
    'database_list' => [
        // Write connection (tag 0)
        [
            'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4;',
            'username' => 'root',
            'password' => 'password',
            'driver_options' => [],
        ],
        // Read connection (tag 1), optional
        [
            'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4;',
            'username' => 'root',
            'password' => 'password',
        ],
    ],
];
```

Also supports passing directly during `RunQuickly`:

```php
\MyApp::RunQuickly([
    'database_list' => [
        ['dsn' => 'sqlite:' . __DIR__ . '/runtime/db.sqlite'],
    ],
]);
```

## Supported Databases

Through PDO drivers, all PDO-compatible databases are supported:

| Driver | DSN Example |
|---|---|
| MySQL | `mysql:host=127.0.0.1;dbname=test;charset=utf8mb4;` |
| SQLite | `sqlite:/path/to/db.sqlite` |
| PostgreSQL | `pgsql:host=localhost;dbname=test` |
| SQL Server | `sqlsrv:Server=localhost;Database=test` |

SQLite relative paths are relative to the `runtime/` directory.

## Usage

### In Controller

```php
use DuckPhp\Foundation\Controller\Helper;

// Get read connection
$rows = Helper::DbForRead()->fetchAll("SELECT * FROM users WHERE status=?", 1);

// Get write connection
Helper::DbForWrite()->execute("UPDATE users SET name=? WHERE id=?", 'new_name', 1);

// Specify database tag
$db = Helper::Db(0);  // Equivalent to DbForWrite
$db = Helper::Db(1);  // Equivalent to DbForRead
```

### In Model

After inheriting `Foundation\Model\Base`, `DbForRead`/`DbForWrite` are automatically available:

```php
use DuckPhp\Foundation\Model\Helper;

Helper::DbForRead()->fetchAll(...);
Helper::DbForWrite()->execute(...);
```

### In Business

The Business layer should obtain database connections through `Foundation\Business\Helper` to maintain layer conventions:

```php
use DuckPhp\Foundation\Business\Helper;

$rows = Helper::DbForRead()->fetchAll("SELECT * FROM users");
```

> **Note**: The Business layer should not directly call `DuckPhp\Component\DbManager` (classes under the `DuckPhp` namespace), but should use them indirectly through `Foundation\Business\Helper`.

## Db Class API

The underlying `DuckPhp\Db\Db` class wraps PDO:

```php
$db = Helper::DbForRead();

$db->fetch($sql, ...$args);        // Get single row (associative array)
$db->fetchAll($sql, ...$args);     // Get multiple rows
$db->fetchColumn($sql, ...$args);  // Get single column value
$db->fetchObject($sql, ...$args);  // Get single row object

$db->execute($sql, ...$args);      // Execute SQL, returns bool (success or not)

$db->quote($value);                // Safe quoting
$db->insertData($table, $data);    // Insert data (associative array)
$db->updateData($table, $id, $data, $pk); // Update data
```

> **Note**: The current implementation of `execute()` returns a `bool`, not the number of affected rows. If you need to accurately determine the number of affected rows, it is recommended to `SELECT` first or use PDOStatement to obtain it yourself. `rowCount()` may not directly reflect the actual number of affected rows in some scenarios.

## Read-Write Separation

- If only one database connection is configured, read and write operations both use it.
- If two connections are configured, `_DbForRead()` uses tag 1 and `_DbForWrite()` uses tag 0.
- `fetch*` series methods use the read connection, `execute` uses the write connection.

## SQL Logging

Enable SQL logging (for debugging):

```php
$options = [
    'database_log_sql_query' => true,
    'database_log_sql_level' => 'debug',
];
```

## Custom Database Class

You can replace the default `Db` class via the `database_class` option:

```php
$options = [
    'database_class' => \MyProject\MyDb::class,
];
```

## Cleaning Up Database Connections

```php
Helper::DbCloseAll();   // Close all database connections
// Or
DbManager::_()->_DbCloseAll();
```
