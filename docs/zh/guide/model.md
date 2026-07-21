# Model（模型层）使用指南

Model 层是 DuckPHP 的数据访问层，负责与数据库交互。每个 Model 类通常对应一张数据库表。

## Model 的定位

```
Controller（输入输出编排）
    ↓
Business（业务逻辑）
    ↓
Model（数据访问）  ← 你在这里
    ↓
Db（数据库连接）
```

Model 层遵循**无状态**原则：不依赖请求上下文，不读写 Session，不接触超全局变量。

## 快速开始

### 创建 Model 类

```php
<?php
namespace MyProject\Model;

use DuckPhp\Foundation\Model\Base;

class UserModel extends Base
{
    // 自动对应表名：user（类名去掉 "Model" 后缀并转小写）
    // 可通过以下属性覆盖：
    // protected $table_name = 'my_user';       // 自定义表名
    // protected $table_prefix = 't_';          // 表前缀
}
```

### 在 Business 中调用

```php
namespace MyProject\Business;

use MyProject\Model\UserModel;

class UserBusiness
{
    public function getUserDetail($id)
    {
        $user = UserModel::_()->getUserInfo($id);
        // ... 业务处理 ...
        return $user;
    }
}
```

## 封装数据方法

`Foundation\Model\Base` 提供的内置方法全部为 **protected**，不对外暴露。你需要在子类中封装公共方法：

### 基础 CRUD

```php
class UserModel extends Base
{
    // 查询单条
    public function getUserInfo($id)
    {
        return $this->find($id);
    }
    
    // 按条件查询
    public function findByEmail($email)
    {
        return $this->find(['email' => $email]);
    }
    
    // 新增
    public function addUser(array $data)
    {
        return $this->add($data);         // 返回自增 ID
    }
    
    // 更新
    public function updateUser($id, array $data)
    {
        return $this->update($id, $data, 'id'); // 第三个参数为主键名
    }
    
    // 删除
    public function deleteUser($id)
    {
        return $this->delete($id);
    }
    
    // 分页列表
    public function getUserList(array $where = [], int $page = 1, int $page_size = 10): array
    {
        return $this->getList($where, $page, $page_size); // 返回 [$total, $data]
    }
}
```

### 自定义 SQL 查询

```php
class UserModel extends Base
{
    // fetchAll：查询多行
    public function searchUsers($keyword)
    {
        return $this->fetchAll(
            "SELECT * FROM `'TABLE'` WHERE name LIKE ? OR email LIKE ?",
            '%' . $keyword . '%',
            '%' . $keyword . '%'
        );
    }
    
    // fetch：查询单行
    public function getLastLoginUser()
    {
        return $this->fetch(
            "SELECT * FROM `'TABLE'` ORDER BY last_login DESC LIMIT 1"
        );
    }
    
    // fetchColumn：取单个值
    public function countActiveUsers()
    {
        return $this->fetchColumn(
            "SELECT COUNT(*) FROM `'TABLE'` WHERE status = ?",
            1
        );
    }
    
    // execute：执行增删改
    public function batchUpdateStatus(array $ids, int $status)
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$status], $ids);
        return $this->execute(
            "UPDATE `'TABLE'` SET status = ? WHERE id IN ($placeholders)",
            ...$params
        );
    }
}
```

> **`'TABLE'` 占位符**：在 SQL 中写 `` `'TABLE'` `` 会自动替换为 `{表前缀}{表名}`（如 `t_user`）。这是模型内置的便捷功能。

### fetchObject / fetchObjectAll

以对象方式获取数据，适合需要类型约束的场景：

```php
class UserDTO
{
    public int $id;
    public string $name;
    public string $email;
}

class UserModel extends Base
{
    public function getUserAsObject($id): ?UserDTO
    {
        return $this->fetchObject(
            "SELECT id, name, email FROM `'TABLE'` WHERE id = ?",
            $id,
            UserDTO::class
        );
    }
    
    public function getAllUsersAsObject(): array
    {
        return $this->fetchObjectAll(
            "SELECT id, name, email FROM `'TABLE'`",
            null,
            UserDTO::class
        );
    }
}
```

## Model 高级用法

### 多表关联示例

Model 可以调用其他 Model，但推荐通过 Business 层编排多表操作：

```php
class OrderModel extends Base
{
    public function getOrderWithItems($orderId)
    {
        $order = $this->find($orderId);
        if (!$order) {
            return null;
        }
        $order['items'] = OrderItemModel::_()->getItemsByOrder($orderId);
        return $order;
    }
}
```

### 分页查询

```php
class ArticleModel extends Base
{
    // 第一个 ? 是 WHERE，第二个 ? 是分页条件，第三个 ? 是 COUNT SQL
    public function getPublishedArticles(int $page = 1, int $size = 10): array
    {
        return $this->getList(
            ['status' => 'published'],  // WHERE 条件
            $page,                       // 当前页码
            $size                        // 每页条数
        );
        // 返回 [$total, $data]
    }
}
```

分页条件支持多种形式：

```php
// 关联数组（等值匹配）
$this->getList(['status' => 1, 'type' => 'article'], $page, $size);

// 纯 SQL WHERE 片段
$this->getList("status = 1 AND type = 'article'", $page, $size);

// 带参数的 WHERE 片段
$this->getList("status = ? AND type = ?", $page, $size, [1, 'article']);
```

### 事务处理

在 Business 层控制事务：

```php
class OrderBusiness
{
    public function createOrder(array $cart, $userId)
    {
        $orderModel = OrderModel::_();
        $itemModel = OrderItemModel::_();
        
        // 开启事务（通过 DbForWrite 获取写连接）
        $db = \DuckPhp\Foundation\Model\Helper::DbForWrite();
        $db->PDO()->beginTransaction();
        try {
            $orderId = $orderModel->addOrder($userId, $cart['total']);
            foreach ($cart['items'] as $item) {
                $itemModel->addItem($orderId, $item);
            }
            $db->PDO()->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $db->PDO()->rollBack();
            throw $e;
        }
    }
}
```

## Foundation\ModelTrait（实例方法，protected）

`DuckPhp\Foundation\ModelTrait` 是集成到 `Base` 类中的核心 Trait，提供所有数据访问方法。这些方法均为 **`protected`**，只能在 Model 子类内部通过 `$this->xxx()` 调用。

### 属性

| 属性 | 类型 | 说明 |
|---|---|---|
| `$table_name` | `?string` | 表名，默认从类名推导（`UserModel` → `user`） |
| `$table_prefix` | `?string` | 表前缀，默认取自 `options['table_prefix']` |
| `$table_pk` | `string` | 主键名，默认 `'id'` |

### 数据查询

| 方法 | 说明 |
|---|---|
| `find($id)` / `find($condition)` | 按主键或条件数组查询单行 |
| `fetchAll($sql, ...$args)` | 参数化查询多行 |
| `fetch($sql, ...$args)` | 参数化查询单行 |
| `fetchColumn($sql, ...$args)` | 取第一行第一列 |
| `fetchObject($sql, ...$args)` | 查询单行映射为 DTO 对象 |
| `fetchObjectAll($sql, ...$args)` | 查询多行映射为 DTO 对象数组 |

### 数据写入

| 方法 | 说明 |
|---|---|
| `add(array $data)` | 插入一行，返回自增 ID |
| `update($id, array $data, ?string $key = null)` | 按主键更新 |
| `execute($sql, ...$args)` | 执行任意 SQL（写连接），返回受影响行数 |

### 分页与辅助

| 方法 | 说明 |
|---|---|
| `getList($where, $page, $page_size)` | 分页查询，返回 `[$total, $data]` |
| `prepare($sql)` | 将 SQL 中 `` `'TABLE'` `` 替换为完整表名 |
| `table()` | 获取完整表名（前缀 + 名称） |

### 自动表名推导

```php
class UserModel extends Base {}           // 表名: user
class OrderItemModel extends Base {}       // 表名: order_item
class AdminLogModel extends Base {}        // 表名: admin_log
```

规则：取类名去掉末尾 `Model` 后，驼峰转蛇形。

### 覆盖表名

```php
class UserModel extends Base
{
    protected $table_name = 'my_user';
    protected $table_prefix = 't_';
    // 最终表名: t_my_user
}
```

## DuckPhp\Helper\ModelHelperTrait（静态方法，public）

`DuckPhp\Helper\ModelHelperTrait` 提供数据库连接获取和 SQL 辅助的静态方法。这些方法为 **`public static`**，可以在任何地方调用，包括不继承 `Base` 的场景。

### 数据库连接

| 方法 | 说明 |
|---|---|
| `Helper::Db($tag = null)` | 获取指定标签的数据库连接 |
| `Helper::DbForRead()` | 获取读连接（标签 1） |
| `Helper::DbForWrite()` | 获取写连接（标签 0） |

### SQL 辅助

| 方法 | 说明 |
|---|---|
| `Helper::SqlForPager($sql, $page, $size)` | 添加 LIMIT/OFFSET |
| `Helper::SqlForCountSimply($sql)` | 转为 COUNT(*) 查询 |

### 使用示例

```php
use DuckPhp\Foundation\Model\Helper;

// 读连接
$rows = Helper::DbForRead()->fetchAll("SELECT * FROM users WHERE status = ?", 1);

// 写连接
Helper::DbForWrite()->execute("UPDATE users SET name = ? WHERE id = ?", 'foo', 1);

// 分页辅助
$sql = Helper::SqlForPager("SELECT * FROM users", $pageNo, $pageSize);
$sql = Helper::SqlForCountSimply("SELECT * FROM users");
```

这两个 Trait 的关系：`Base` 类同时使用了 `ModelTrait` 和 `ModelHelperTrait`，因此子类中既可以 `$this->find()`（来自 ModelTrait），也可以 `static::DbForRead()`（继承自 ModelHelperTrait）。

## 编写规范

1. **一个 Model 类对应一张表**，类名 = 表名（驼峰转蛇形）+ `Model` 后缀
   - `user` → `UserModel`
   - `order_item` → `OrderItemModel`

2. **Model 不调 Model**：一个 Model 调用另一个 Model 虽然技术上可行，但建议通过 Business 层编排

3. **Model 不做格式转换**：输出格式（如日期格式、金额显示）应在 Business 层或 View 层处理

4. **复杂查询封装为命名清晰的方法**：`searchByStatusAndDate()` 比 `fetchAll()` 更可读

5. **事务控制在 Business 层**，Model 层只负责单表操作

## 配置数据库连接

参见 [数据库](database.md) 一章。
