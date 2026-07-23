# DuckPhp\GlobalUser\UserControllerInterface

用户控制器接口。

## 简介

`UserControllerInterface` 是一个标记接口（无方法定义）。用于标识控制器类属于前台用户端，便于框架或中间件进行身份校验。

## 接口定义

```php
namespace DuckPhp\GlobalUser;

interface UserControllerInterface
{
}
```

## 使用方式

```php
namespace YourProject\Controller;

use DuckPhp\GlobalUser\UserControllerInterface;

class UserProfileController extends Base implements UserControllerInterface
{
    // 实现此接口后，框架可在路由阶段自动执行用户身份检查
}
```

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)
- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md)
