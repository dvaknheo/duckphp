# DuckPhp\Foundation\SimpleModelTrait

简单模型类 Trait。

## 简介

`DuckPhp\Foundation\SimpleModelTrait` 是模型层类的简化组合 Trait。它提供了基于类名自动推导表名、读写分离数据库访问、常用 CRUD 操作以及 SQL 执行能力，适合快速实现数据模型。

## 选项

### 受保护属性

| 属性 | 默认值 | 说明 |
|---|---|---|
| `$table_name` | `null` | 表名。未设置时根据类名自动推导 |
| `$table_prefix` | `null` | 表前缀。未设置时读取 `App::Current()->options['table_prefix']` |
| `$table_pk` | `'id'` | 主键名 |

## 使用方式

### 在模型类中使用

```php
use DuckPhp\Foundation\SimpleModelTrait;

class UserModel
{
    use SimpleModelTrait;
}
```

### 自动表名

类名 `UserModel` 会自动推导为 `user`。

### 基础 CRUD

```php
$model = UserModel::_();

// 获取表名
$table = $model->table();

// 查询列表
[$total, $list] = $model->getList(['status' => 1], $page, $pageSize);

// 按主键查找
$row = $model->find(1);

// 添加
$model->add(['name' => 'DuckPhp']);

// 更新
$model->update(1, ['name' => 'Duck']);
```

### 自定义 SQL

```php
$rows = $model->fetchAll('SELECT * FROM `{TABLE}` WHERE status = ?', 1);
$row = $model->fetch('SELECT * FROM `{TABLE}` WHERE id = ?', 1);
$count = $model->fetchColumn('SELECT COUNT(*) FROM `{TABLE}`');
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'table_prefix' => 'dp_',
    ];
}
```

## 注意事项

1. 该 Trait 使用 `SingletonTrait` 和 `ZCallTrait`，支持单例和快速调用。
2. 数据库操作依赖 `DuckPhp\Component\DbManager`，需确保数据库已配置。
3. `prepare($sql)` 方法会将 SQL 中的 `` `{TABLE}` `` 替换为当前表名。
4. `find($a)` 支持传入标量（按主键）或关联数组（多条件查询）。

## 方法列表

### 公共方法

| 方法 | 说明 |
|---|---|
| `table()` | 获取完整表名（前缀 + 表名） |
| `prepare($sql)` | 将 SQL 中的 `` `{TABLE}` `` 替换为当前表名 |

### 受保护方法

| 方法 | 说明 |
|---|---|
| `getTableNameByClass($class)` | 根据类名推导表名 |
| `getTablePrefixByClass($class)` | 根据配置获取表前缀 |
| `getList($where = [], int $page = 1, int $page_size = 10)` | 分页查询列表 |
| `find($a)` | 按主键或条件查找单条记录 |
| `add($data)` | 插入数据 |
| `update($id, $data, $key = null)` | 更新数据 |
| `execute($sql, ...$args)` | 执行写操作 SQL |
| `fetchAll($sql, ...$args)` | 查询多条 |
| `fetch($sql, ...$args)` | 查询单条 |
| `fetchColumn($sql, ...$args)` | 查询单列 |
| `fetchObject($sql, ...$args)` | 查询单条并映射为对象 |
| `fetchObjectAll($sql, ...$args)` | 查询多条并映射为对象 |

## 相关链接

- [DuckPhp\Component\DbManager](Component-DbManager.md)
- [DuckPhp\Component\ZCallTrait](Component-ZCallTrait.md)
- [DuckPhp\Core\SingletonTrait](Core-SingletonTrait.md)
