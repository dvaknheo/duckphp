# DuckPhp\Foundation\Business\Base

## 简介

`Business\Base` 是工程「Business（业务层）」的推荐基类（abstract）。源码极简：`use SingletonTrait`，让业务类可通过 `类名::_()` 单例访问。

业务层约定为“纯逻辑、无状态处理请求上下文”，本基类不绑定任何框架 API——业务所需的设置/缓存/校验等由 [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) 提供。

## 类信息

- 命名空间：`DuckPhp\Foundation\Business`
- 声明：`abstract class Base`
- 使用的 Trait：`DuckPhp\Foundation\SingletonTrait`

## 使用方式

```php
namespace MyProject\Business;

use DuckPhp\Foundation\Business\Base;

class UserBusiness extends Base
{
    public function getList()
    {
        // 业务逻辑；可调 Model
    }
}
// 控制器内：UserBusiness::_()->getList()
```

## 注意事项

- 本类未定义任何业务方法；业务方法由子类提供。
- 分层约定见 `docs/zh/guide/layers.md`：Business 只依赖 System 层与 Model，不直接触碰请求上下文。

## 方法列表

本类为空抽象基类，未额外声明方法（`_()` 来自 SingletonTrait）。

## 相关链接

- [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) — 业务静态助手
- [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) — 单例入口来源
