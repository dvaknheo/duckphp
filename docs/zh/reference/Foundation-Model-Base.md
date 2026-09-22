# DuckPhp\Foundation\Model\Base

## 简介

`Model\Base` 是工程「Model（数据层）」的推荐基类（abstract）。它组合两个 Trait：

- `ModelTrait`：表名约定与常用的查改增删封装（`table()/prepare()/getList()/find()/add()/update()/execute()/fetch*` 等）；
- `ModelHelperTrait`：把 `DbManager` 入口折叠成 `Db()/DbForRead()/…` 静态方法。

工程的数据模型类继承本基类即可获得“按类名推表名 + 读写分离 + `'TABLE'` 宏替换”等能力。

## 类信息

- 命名空间：`DuckPhp\Foundation\Model`
- 声明：`abstract class Base`
- 使用的 Trait：`DuckPhp\Foundation\Model\ModelTrait`、`DuckPhp\Foundation\Model\ModelHelperTrait`

## 使用方式

```php
namespace MyProject\Model;

use DuckPhp\Foundation\Model\Base;

class UserModel extends Base
{
    public function getUser($id)
    {
        return $this->find($id);          // 按主键查（表名按类名 UserModel→user 推断）
    }
    public function page($where = [], $page = 1)
    {
        return $this->getList($where, $page, 10); // [总数, 数据]
    }
}
```

## 注意事项

- 表名/表前缀规则见 `ModelTrait`（类名去尾部 `Model` 转小写 + `table_prefix` 选项）。
- 读走 `DbForRead()`、写走 `DbForWrite()`，符合读写分离约定。

## 方法列表

本类为组合基类，未额外声明方法（方法由 `ModelTrait` 与 `ModelHelperTrait` 提供，分别见对应文档）。

## 相关链接

- [DuckPhp\Foundation\Model\ModelTrait](Foundation-Model-ModelTrait.md) — 表级 CRUD/查询封装
- [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) — 数据层静态助手
- [DuckPhp\Foundation\Model\ModelHelper](Foundation-Model-ModelHelper.md) — Model 静态助手类
