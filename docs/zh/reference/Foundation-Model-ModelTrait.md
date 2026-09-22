# DuckPhp\Foundation\Model\ModelTrait

## 简介

`ModelTrait` 是数据模型（Model）的常用能力封装，被 `Foundation\Model\Base` 组合。它提供：

- **表名约定**：`table()` 按类名推断表名（类短名去掉尾部 `Model` 并转小写，如 `UserModel` → `user`），可加 `table_prefix`（来自 `App::options['table_prefix']`）；也支持手工设置 `$table_name/$table_prefix/$table_pk`。
- **`'TABLE'` 宏替换**：`prepare()` 把 SQL 中的 `` `'TABLE'` `` 替换为实际表名。
- **通用查询/写操作**（都走读写分离）：`getList()`（分页）、`find()`、`add()`、`update()`、`execute()`、`fetch*()`（fetchAll/fetch/fetchColumn/fetchObject/fetchObjectAll）。

## 类信息

- 命名空间：`DuckPhp\Foundation`
- 声明：`trait ModelTrait`
- 使用的 Trait：`DuckPhp\Core\SingletonExTrait`
- 使用方：`DuckPhp\Foundation\Model\Base`

## 使用方式

```php
namespace MyProject\Model;

use DuckPhp\Foundation\Model\ModelTrait;

class OrderModel
{
    use ModelTrait;

    public function myOrders($uid)
    {
        return $this->fetchAll("select * from `'TABLE'` where uid = ?", $uid);
    }
}

$m = OrderModel::_();
echo $m->table();                  // 'order'（可带 table_prefix）
$m->myOrders(7);
```

## 注意事项

- **读写分离**：查询类（`fetch*`/`getList`/`find`）走 `_DbForRead()`；写类（`add`/`update`/`execute`）走 `_DbForWrite()`。
- 表名/前缀属性在 `table()` 首次调用时惰性初始化：`$table_prefix` 来自 `App::options['table_prefix']`，`$table_name` 由类名推断；子类可直接给 `protected $table_name = 'user';` 覆盖。
- `getList($where,$page,$page_size)` 返回 `[$total, $data]`，`$where` 用 `quoteAndArray` 生成（键=值），默认按 `id desc` 排序（写死在 SQL 里）。
- `find($a)`：标量视为按 `$table_pk`（默认 `id`）查；数组视为等值条件（以 `and ` 连接）。
- `fetchObject/fetchObjectAll` 把结果类设为 `static::class`（即模型类本身，通常为属性公开的简单模型）。
- 表级写操作直接复用 `DbAdvanceTrait` 的 `insertData/updateData` 语义（`add` 返回自增 ID 等）。

## 方法列表

### 公共方法

    public function table(): string
返回实际表名（`$table_prefix + $table_name`，惰性推算）。

    public function prepare(string $sql): string
把 SQL 中的 `` `'TABLE'` `` 宏替换为实际表名。

### 受保护方法

    protected function getTableNameByClass(string $class): string
按类名推断表名（类短名去尾部 `Model` 后小写）。

    protected function getTablePrefixByClass(string $class): string
取表前缀（`App::options['table_prefix']`）。

    protected function getList(array $where = [], int $page = 1, int $page_size = 10): array
分页查询（走读库），返回 `[$total, $data]`。

    protected function find($a)
按主键或等值条件查一行（走读库）。

    protected function add(array $data)
插入一行（走写库，返回自增 ID 或 execute 结果）。

    protected function update($id, array $data, ?string $key = null)
按主键更新一行（走写库）。

    protected function execute(string $sql, ...$args)
执行写 SQL（走写库，并预置表名）。

    protected function fetchAll(string $sql, ...$args): array
查全部行（读库 + 表名）。

    protected function fetch(string $sql, ...$args)
查一行（读库 + 表名）。

    protected function fetchColumn(string $sql, ...$args)
查单列值（读库 + 表名）。

    protected function fetchObject(string $sql, ...$args)
查一行对象（结果类为 `static::class`）。

    protected function fetchObjectAll(string $sql, ...$args): array
查多行对象（结果类为 `static::class`）。

## 相关链接

- [DuckPhp\Foundation\Model\Base](Foundation-Model-Base.md) — 组合本 Trait 的模型基类
- [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md) — insertData/updateData 等底层实现
- [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) — 配套静态助手
