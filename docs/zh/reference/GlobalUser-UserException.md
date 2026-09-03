# DuckPhp\GlobalUser\UserException

## 简介

`UserException` 是用户（前台）领域的异常基类，继承框架系统异常 `DuckPhp\Core\DuckPhpSystemException`。用户动作/服务实现抛出与前台登录、权限相关的业务异常时可使用本类（或其子类），便于上层按异常类型统一处理。

源码中该类为空类（`class UserException extends DuckPhpSystemException { }`），本身不增加行为；所有能力继承自父类（含 `ThrowOn` 静态快捷抛异常等）。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`class UserException extends DuckPhp\Core\DuckPhpSystemException`

## 使用方式

```php
use DuckPhp\GlobalUser\UserException;

throw new UserException('请先登录');

// 或用 DuckPhpSystemException 提供的静态快捷方式：
UserException::ThrowOn(!$logined, '请先登录', 401);
```

## 注意事项

- 继承链：`UserException → DuckPhpSystemException → \Exception`，并通过 `ThrowOnTrait` 获得 `ThrowOn()` 静态方法。
- 需要区分“前台用户异常”与“后台管理员异常”时，分别使用 `UserException` 与 `AdminException`。

## 方法列表

本类为空类，未额外声明方法（继承父类与 PHP `\Exception` 的全部能力）。

## 相关链接

- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) — 父类
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 用户组件
