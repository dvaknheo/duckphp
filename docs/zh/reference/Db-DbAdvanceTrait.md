# DuckPhp\Db\DbAdvanceTrait

## 简介

`DbAdvanceTrait` 是为 `Db` 连接的“便捷数据操作”补充的方法集合，被 `DuckPhp\Db\Db` 以 `use DbAdvanceTrait` 组合。它依赖宿主类已具备的能力：`quote()`（引用）、`quoteScheme()`（标识符外壳）、`$pdo`（PDO 实例）、`fetch()`/`execute()`/`fetchAll()`（基础读写）。

主要内容分两类：

- **SQL 片段生成**：`quoteIn()`、`quoteSetArray()`、`quoteAndArray()`、`quoteInsertArray()`；
- **表级便捷操作**：`findData()`、`insertData()`、`updateData()`、`deleteData()`（默认软删除 `is_deleted=1`）；
- 分页/计数辅助：`_SqlForPager()`、`_SqlForCountSimply()`。

## 类信息

- 命名空间：`DuckPhp\Db`
- 声明：`trait DbAdvanceTrait`
- 使用方：`DuckPhp\Db\Db`（`use DbAdvanceTrait`）

## 使用方式

```php
// 假设 $db 是 Db 实例（组合了本 Trait）
$db->table('user');

$id = $db->insertData('user', ['name' => 'duck', 'age' => 18]);   // 返回 lastInsertId
$db->updateData('user', $id, ['name' => 'duck2']);
$row = $db->findData('user', $id);
$db->deleteData('user', $id);                                      // 默认软删：set is_deleted=1
$db->deleteData('user', $id, 'id', null);                          // 传 null 则真删

$sql = $db->_SqlForPager('select * from log', 2, 10);              // … LIMIT 10,10
$cnt = $db->fetchColumn($db->_SqlForCountSimply('select * from log'));
```

### 拼 SQL 片段

```php
$in   = $db->quoteIn([1, 2, 3]);          // '1','2','3'（空数组返回 NULL）
$set  = $db->quoteSetArray(['name' => 'a', 'age' => 1]);   // `name`='a',`age`='1'
$and  = $db->quoteAndArray(['a' => 1]);   // `a`='1'
$ins  = $db->quoteInsertArray(['name' => 'a', 'age' => 1]); // (`name`,`age`)VALUES('a','1')
```

## 注意事项

- 本 Trait 是“纯附加”，不要求宿主继承 `ComponentBase`，只要求宿主提供上面列出的成员。
- `deleteData()` 第 4 参 `$key_delete` 默认 `'is_deleted'`：非空则执行 `update … set is_deleted=1`（软删）；传空值（如 `null`）才执行真正的 `delete`。
- `insertData()` 第 3 参 `$return_last_id` 为 `true` 时返回 `lastInsertId()`，否则返回 `execute()` 的布尔结果。
- `updateData()` 会先把数据中的主键字段（默认 `$key='id'`）移除，再生成 `SET` 片段。
- `quoteSetArray()` 与 `quoteAndArray()` 的区别仅在于片段连接符（`,` vs `and`），且 `quoteAndArray` 以 `and` 作连接（间隔不含前导空格，为源码原样行为）。
- `_SqlForCountSimply()` 用正则把 `select … from` 头部替换为 `SELECT COUNT(*) as c FROM`（不处理子查询/复杂 SQL，只适用简单查询）。

## 方法列表

### 公共方法

    public function quoteIn(array $array): string
把数组转成“引用后的逗号串”（如 `'a','b'`）；空数组返回 `NULL`。

    public function quoteSetArray(array $array): string
生成 SET 片段：`标识符=引用值,标识符=引用值…`（用于 `update … set`）。

    public function quoteAndArray(array $array): string
生成 WHERE 片段：`标识符=引用值 and 标识符=引用值…`（源码以 `and` 连接，无前导空格）。

    public function quoteInsertArray(array $array): string
生成 INSERT 值片段：`(`key`,`key`)VALUES('v','v')`；空数组返回空串。

    public function findData($table_name, $id, $key = 'id')
按主键查一行：`select * from 表 where key=? limit 1`，返回 `fetch()` 的关联数组。

    public function insertData($table_name, $data, $return_last_id = true)
插入一行：默认返回 `lastInsertId()`；`$return_last_id=false` 时返回 `execute()` 结果。

    public function deleteData($table_name, $id, $key = 'id', $key_delete = 'is_deleted')
删除一行：`$key_delete` 非空做软删（`set is_deleted=1`），否则真删。

    public function updateData($table_name, $id, $data, $key = 'id')
按主键更新一行：先移除 `$data` 中的主键字段，再 `update … set … where key=?`。

    public function _SqlForPager($sql, $page_no, $page_size = 10)
给 SQL 追加分页：`LIMIT start,page_size`（`start=(page_no-1)*page_size`）。

    public function _SqlForCountSimply($sql)
把简单查询的 `select … from` 头替换为 `SELECT COUNT(*) as c FROM`，用于快速取总数。

## 相关链接

- [DuckPhp\Db\Db](Db-Db.md) — 组合本 Trait 的连接实现
- [DuckPhp\Db\DbInterface](Db-DbInterface.md) — Db 的基础契约
