# DuckPhp\GlobalUser\UserException

## 简介

`UserException` 是用户（前台）领域的异常类：`class UserException extends \Exception`（PHP 内置异常，**不再**继承 `DuckPhp\Core\DuckPhpSystemException`）。

`GlobalUser` 在“已配置会话但未登录”等场景下会抛出它（如 `id()`/`name()` 的 `"NoLogin"` 分支），便于上层把“前台未登录/无权限”与其它异常区分处理。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`class UserException extends \Exception`

## 使用方式

```php
use DuckPhp\GlobalUser\UserException;

throw new UserException('请先登录');

// 框架内部示例（GlobalUser::id()）：
// CoreHelper::ControllerThrowOn($check_login && !$id, "id(): NoLogin", -1, UserException::class);
```

## 注意事项

- 该类为空类，本身不增加行为；能力来自 PHP `\Exception`。
- **不再**通过 `DuckPhpSystemException` 获得 `ThrowOn()` 静态方法（继承链已改为直接继承 `\Exception`）；需要“条件抛”能力时可用 `CoreHelper` 系列（`ProjectThrowOn`/`BusinessThrowOn`/`ControllerThrowOn`）或自行在自定义异常上 `use DuckPhp\Core\ThrowOnTrait`。
- 与后台侧的 `AdminException`（仍继承 `DuckPhpSystemException`）不同，两者继承链不一致，使用前请以源码为准。

## 方法列表

本类为空类，未额外声明方法（继承 PHP `\Exception` 的全部能力）。

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 抛出本异常的用户组件
- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) — 系统异常基类（本类不再继承它）
- [DuckPhp\GlobalAdmin\AdminException](GlobalAdmin-AdminException.md) — 后台侧异常（继承 DuckPhpSystemException）
