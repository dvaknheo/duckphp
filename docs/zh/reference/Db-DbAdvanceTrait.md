# DuckPhp\Db\DbAdvanceTrait

数据库高级操作 Trait。

## 简介

`DbAdvanceTrait` 提供了一组常用的数据库辅助方法，包括字符串转义、IN 条件生成、批量插入、简单的 CRUD 操作，以及分页 SQL 生成。这些方法被混入 `DuckPhp\Db\Db` 类中。

## 使用方式

### 字符串数组转义

```php
use DuckPhp\Component\DbManager;

$db = DbManager::Db();
$in = $db->quoteIn(['admin', 'user', 'guest']);
// 生成：'admin','user','guest'

$users = $db->fetchAll("SELECT * FROM users WHERE role IN ($in)");
```

### 生成 SET 片段

```php
$set = $db->quoteSetArray(['name' => 'Tom', 'age' => 20]);
// 生成：`name`='Tom',`age`='20'
```

### 生成 INSERT 语句

```php
$insert = $db->qouteInsertArray(['name' => 'Tom', 'age' => 20]);
// 生成：(`name`,`age`)VALUES('Tom','20')
```

### 简单 CRUD

```php
// 查询
$user = $db->findData('users', 1);

// 插入
$id = $db->insertData('users', ['name' => 'Tom', 'age' => 20]);

// 更新
$db->updateData('users', 1, ['name' => 'Jerry']);

// 软删除（默认使用 is_deleted 字段）
$db->deleteData('users', 1);

// 硬删除
$db->deleteData('users', 1, 'id', false);
```

### 分页 SQL

```php
$sql = "SELECT * FROM posts";
$pageSql = $db->_SqlForPager($sql, 2, 10);
// 生成：SELECT * FROM posts LIMIT 10,10

$countSql = $db->_SqlForCountSimply($sql);
// 生成：SELECT COUNT(*) as c FROM posts
```

## 注意事项

1. `quoteIn()` 对空数组会返回 `'NULL'`，使用时应避免在 IN 子句中直接使用。
2. `quoteSetArray()` 和 `qouteInsertArray()` 会自动处理字段名和值引用。
3. `deleteData()` 默认使用软删除（`is_deleted=1`），如需物理删除请将 `$key_delete` 传 `false` 或空字符串。
4. `_SqlForCountSimply()` 使用正则替换 SELECT 字段为 `COUNT(*)`，对复杂 SQL 可能不适用。

## 方法列表

### 公共方法

    public function quoteIn(array $array): string
将数组元素转义并用逗号拼接，用于 IN 条件

    public function quoteSetArray(array $array): string
生成 UPDATE 的 SET 片段

    public function quoteAndArray(array $array): string
生成 WHERE 的 AND 条件片段

    public function qouteInsertArray(array $array): string
生成 INSERT 的键值片段

    public function findData($table_name, $id, $key = 'id')
根据主键查询单行数据

    public function insertData($table_name, $data, $return_last_id = true)
插入数据，默认返回自增 ID

    public function deleteData($table_name, $id, $key = 'id', $key_delete = 'is_deleted')
根据主键删除或软删除数据

    public function updateData($table_name, $id, $data, $key = 'id')
根据主键更新数据

    public function _SqlForPager($sql, $page_no, $page_size = 10)
为 SQL 添加 LIMIT 分页子句

    public function _SqlForCountSimply($sql)
将 SELECT 查询转换为 COUNT 查询

## 相关链接

- [DuckPhp\Db\Db](Db-Db.md)
- [DuckPhp\Db\DbInterface](Db-DbInterface.md)
- [DuckPhp\Component\DbManager](Component-DbManager.md)
