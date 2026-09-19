# 2-6 模型层

> 解决什么问题：模型里到底该写什么、[`ModelTrait`](../reference/Foundation-ModelTrait.md) 白送了什么、为什么它的 CRUD 方法是 `protected`、表名怎么推导、模型该向业务层暴露什么。
> 前置：[第 2-1 章 四层架构与调用规范](layers.md)、[第 2-5 章 数据库](database.md)。预计 20 分钟。
> 示例：`demo/src/Model/`（`Base.php` / `DemoModel.php`）、`demo/public/dbtest.php`（完整可跑：模型 + 分页 + 增删改查）、`tests/data_for_tests/ZAllDemo/src/Model/`。

```bash
php -S 127.0.0.1:8080 -t demo/public
# 打开 http://127.0.0.1:8080/dbtest.php 看模型层跑完整套 CRUD
```

## 最小示例

两层最小骨架（`demo/src/Model/` 的真实文件）：

```php
// demo/src/Model/Base.php —— 工程自己的模型基类
namespace MyProj\Model;

use DuckPhp\Foundation\ModelTrait;

class Base
{
    use ModelTrait;
}
```

```php
// demo/src/Model/DemoModel.php —— 一个具体模型
namespace MyProj\Model;

class DemoModel extends Base
{
    public function testdb()
    {
        $sql = "select 1+? as t";
        return Helper::Db()->fetch($sql, 2);       // 模型里可以直接用 Db
    }
}
```

带完整表操作的模型长这样（`demo/public/dbtest.php` 的 `TestModel`，可直接访问该页面实跑）：

```php
class TestModel
{
    use ModelTrait;
    public function __construct()
    {
        $this->table_name = 'test';                // 表名（不含前缀）
    }
    public function getDataList($page, $pagesize)
    {
        $sql = "select * from `'TABLE'` order by id desc";
        $total = $this->fetchColumn(Helper::SqlForCountSimply($sql));            // 读连接
        $list  = $this->fetchAll(Helper::SqlForPager($sql, $page, $pagesize));   // 读连接
        return [$total, $list];
    }
    public function addData($data)
    {
        $this->execute("insert into `'TABLE'` (content) values(?)", $data['content']); // 写连接
        return Helper::Db()->lastInsertId();
    }
}
```

## 机制说明

### 1. 模型的定位：只做数据访问

模型层是四层里最窄的一层：**把表变成方法**，不判断业务、不抛业务异常、不读请求上下文——业务规则属于 Business（[第 2-1 章](layers.md)的越界矩阵）。

```php
// ✅ 模型：只回答「数据是什么」
public function findByStatus(string $status): array
{
    return $this->fetchAll("select * from `'TABLE'` where status=?", $status);
}

// ❌ 模型里不该有的：业务判断、权限校验、抛业务异常、读 $_GET
```

### 2. 两条路线

| 路线 | 写法 | 适用 |
|---|---|---|
| **用 `ModelTrait`** | `use ModelTrait;` | 常规单表模型：表名宏、读写分流、分页都接好了 |
| **直接用 [`Db`](../reference/Db-Db.md)** | 自己的类里调 `Helper::Db()` / `Helper::DbForRead()` | 跨多表复杂查询、报表、要精细控制 SQL 的场景 |

两条可以混用：`demo/src/Model/DemoModel.php` 既继承了带 `ModelTrait` 的 `Base`，也在方法里直接 `Helper::Db()->fetch(...)`。

不继承 `Base` 也能用 `ModelTrait`（`dbtest.php` 的 `TestModel` 就是这么干的）——它是 trait，不是必须的基类。

### 3. `ModelTrait` 给了什么

| 成员 | 可见性 | 说明 |
|---|---|---|
| `$table_name` | protected 属性 | 表名（不含前缀）；不设就按类名推导 |
| `$table_prefix` | protected 属性 | 表前缀；不设则取应用选项 `table_prefix` |
| `$table_pk` | protected 属性 | 主键名，默认 `'id'` |
| `table()` | **public** | 返回「前缀 + 表名」，如 `app_note` |
| `prepare($sql)` | **public** | 把 SQL 里的 `` `'TABLE'` `` 宏换成 `table()` |
| `getList($where, $page, $page_size)` | protected | 返回 `[$total, $data]` |
| `find($id_or_where)` | protected | 按主键或条件数组查一行 |
| `add($data)` | protected | 插入，返回自增 id |
| `update($id, $data, $key)` | protected | 按主键更新 |
| `execute($sql, ...$args)` | protected | 写操作（走**写**连接） |
| `fetchAll` / `fetch` / `fetchColumn` / `fetchObject` / `fetchObjectAll` | protected | 读操作（走**读**连接，自动绑定表名宏） |
| `::_()`（来自 [`SingletonExTrait`](../reference/Core-SingletonExTrait.md)） | public | 可变单例，可被覆盖（[第 3-5 章](overriding.md)） |

表名推导（`getTableNameByClass()`）：类名去掉结尾的 `Model` 再转小写——`NoteModel` → `note`、`UserProfileModel` → `userprofile`。对不上就在构造函数里显式设 `$this->table_name`。

### 4. 为什么 CRUD 方法是 `protected`（重要）

```php
NoteModel::_()->add($data);        // ❌ Call to protected method
```

这是**刻意设计**：框架只给「在模型内部拼 SQL」的原语，对外暴露什么由你决定。所以每个模型都该写自己的 public 方法：

```php
class NoteModel extends Base
{
    public function __construct() { $this->table_name = 'note'; }

    public function create(array $data): int                  // 对外只开这一个入口
    {
        return (int)$this->add($data);
    }
    public function paginate(int $page, int $size = 10): array
    {
        return $this->getList([], $page, $size);              // [total, data]
    }
    public function findById(int $id): ?array
    {
        return $this->find($id) ?: null;
    }
}
```

好处很实际：Business 拿不到 `execute()` 这种万能入口，就没法绕过模型写裸 SQL——边界从「约定」变成了「语言层面的可见性」。

### 5. `ModelHelperTrait`：模型可用的静态工具

| 方法 | 用途 |
|---|---|
| `Helper::Db($tag)` | 取连接（不传 = 写连接，`1` = 读连接） |
| `Helper::DbForRead()` / `Helper::DbForWrite()` | 明确的读/写连接 |
| `Helper::SqlForPager($sql, $page, $size)` | 给 SQL 加分页 |
| `Helper::SqlForCountSimply($sql)` | 把 SQL 转成计数 SQL |
| `Helper::DatabaseDriver()` | 当前驱动名（写驱动分支时用） |

工程侧的 `Model\Helper`（`demo/src/Model/Helper.php`）就是 [`use ModelHelperTrait;`](../reference/Helper-ModelHelperTrait.md) 一行。

### 6. 跨库/多连接

模型层没有「跨库模型」这种东西——要访问第二个库就按 tag 取连接（[第 2-5 章](database.md)）：

```php
class LogModel extends Base
{
    public function __construct() { $this->table_name = 'log'; }

    public function recent(int $n): array
    {
        return Helper::Db(1)->fetchAll('select * from `log` order by id desc limit ' . (int)$n);
    }
}
```

> 提醒：`demo/src/Model/CrossModelEx.php` 这名字听起来像「跨库模型」，但它的内容只是个 `foo()` 空壳样板——**不要从名字推断用法**，跨库按上面的 tag 写法来。

### 7. 和业务层的接口约定

模型对外的方法按「业务需要什么」来命名，而不是把表的字段全铺出去：

```php
// Business 里
$total = NoteModel::_()->countByUser($userId);
$rows  = NoteModel::_()->listByUser($userId, $page);
// 视图层配合分页：Helper::PageHtml($total)（第 2-5 章）
```

## 常见写法

**① 一个模型一张表，方法名说业务**

```php
public function countByUser(int $userId): int
{
    return (int)$this->fetchColumn("select count(*) from `'TABLE'` where user_id=?", $userId);
}
```

**② 软删**

```php
Helper::Db()->deleteData('note', $id);          // DbAdvanceTrait 的软删，默认写 is_deleted
// 或自己在模型里写：update ... set is_deleted=1 where id=?
```

**③ 通用查询抽到工程基类，别复制粘贴**

```php
abstract class Base
{
    use ModelTrait;

    public function paginate(int $page, int $size = 10): array
    {
        return $this->getList([], $page, $size);       // 所有模型共用
    }
}
```

**④ 只读模型显式走读连接**

```php
public function hotList(int $n): array
{
    return Helper::DbForRead()->fetchAll("select * from `'TABLE'` order by views desc limit " . (int)$n);
}
```

**⑤ 事务放业务层，模型只提供原子操作**

```php
// Business 里
$pdo = Helper::Db()->PDO();
$pdo->beginTransaction();
try {
    OrderModel::_()->create($order);
    OrderItemModel::_()->createMany($items);
    $pdo->commit();
} catch (\Throwable $ex) {
    $pdo->rollBack();
    throw $ex;                 // 交给异常机制（第 2-11 章）
}
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `Call to protected method ...::add()` | `ModelTrait` 的 CRUD 是 protected（刻意设计） | 在模型里写 public 包装方法对外暴露 |
| 找不到表 | 类名推导不合预期（`UserProfileModel` → `userprofile`） | 构造函数里显式 `$this->table_name = 'user_profile'` |
| 表名前缀没生效 | SQL 里手写表名，没走 `` `'TABLE'` `` 宏 | 用宏，或调 `$this->prepare($sql)` |
| 模型里抛业务异常 / 写权限判断 | 越界：那是 Business 的活 | 挪到 Business，模型只返回数据 |
| Business 里出现 `Helper::Db()` 裸 SQL | 越界：绕过模型层 | 把 SQL 收进模型，业务只调模型方法 |
| 读连接查不到刚写的数据 | 读写分离延迟，`fetch*` 默认走读连接 | 需要强一致时显式用写连接 |
| 模型变成几百行「上帝类」 | 一张表堆了太多业务语义 | 判断逻辑回 Business，按业务拆方法 |
| 覆盖模型类后没生效 | 用了 `new NoteModel()` 而不是 `NoteModel::_()` | 一律 `::_()`（覆盖依赖容器，[第 3-5 章](overriding.md)） |

## 下一步

- [第 2-8 章 表单与数据验证](validator.md)：入库前的校验放在业务层。
- [第 2-11 章 异常与错误处理](exception.md)：数据层错误怎么变成用户看得懂的响应。
- [第 3-5 章 重写与覆盖](overriding.md)：模型/控制器的覆盖与替换。
- 参考手册：[DuckPhp\Foundation\ModelTrait](../reference/Foundation-ModelTrait.md)、[DuckPhp\Helper\ModelHelperTrait](../reference/Helper-ModelHelperTrait.md)、[DuckPhp\Db\DbAdvanceTrait](../reference/Db-DbAdvanceTrait.md)。
