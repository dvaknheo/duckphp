# DuckPhp\GlobalAdmin\AdminControllerInterface

管理员控制器接口。

## 简介

`AdminControllerInterface` 是一个标记接口（无方法定义）。用于标识控制器类属于管理员后端，便于框架或中间件进行身份校验。

## 接口定义

```php
namespace DuckPhp\GlobalAdmin;

interface AdminControllerInterface
{
}
```

## 使用方式

```php
namespace YourProject\Controller;

use DuckPhp\GlobalAdmin\AdminControllerInterface;

class AdminUserController extends Base implements AdminControllerInterface
{
    // 实现此接口后，框架可在路由阶段自动执行管理员身份检查
}
```

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md)
