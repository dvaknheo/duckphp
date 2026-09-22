# DuckPhp\GlobalAdmin\AdminException

## 简介

`AdminException` 是管理员（后台）领域的异常类：`class AdminException extends \Exception`（PHP 内置异常）。管理员动作/服务实现抛出与后台相关的业务异常时使用本类（或其子类），便于上层按异常类型统一处理；[AdminControllerBase](Foundation-Controller-AdminControllerBase.md) 在 Ajax 无权场景就是抛它。

它**不继承** `DuckPhp\Core\DuckPhpSystemException`——后者只用于框架内部的系统级错误（见 [Core-DuckPhpSystemException](Core-DuckPhpSystemException.md) 的硬性说明）。为了保留守卫式抛法，本类自己 `use DuckPhp\Ext\ThrowOnTrait;`。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`class AdminException extends \Exception`
- 使用 Trait：`DuckPhp\Ext\ThrowOnTrait`（提供 `ThrowOn()`）

## 使用方式

```php
use DuckPhp\GlobalAdmin\AdminException;

throw new AdminException('后台权限不足');

// 守卫式（来自 ThrowOnTrait，不是继承 DuckPhpSystemException 得来的）：
AdminException::ThrowOn(!$allowed, '后台权限不足', 403);
```

## 注意事项

- 继承链：`AdminException → \Exception`；`ThrowOn()` 来自本类 `use` 的 `ThrowOnTrait`（`throw new static($message, $code)`）。
- 与用户侧的 [UserException](GlobalUser-UserException.md) 对称：两者都直接继承 `\Exception`，都不属于「框架系统异常」。
- 想按类型单独捕获后台异常就 catch 本类；`exception_map` 之类的映射可按工程需要配置。
- 判断该抛哪一类：框架自己出的问题 → `DuckPhpSystemException`；工程的权限/业务问题 → 本类（或你自己的异常类）。

## 方法列表

本类未显式声明方法，可用能力来自：

- `DuckPhp\Ext\ThrowOnTrait::ThrowOn($flag, $message, $code = 0)` —— 首位为真时 `throw new static(...)`。
- PHP 内置 `\Exception` 的全部能力（`getMessage()`、`getCode()` 等）。

## 相关链接

- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) — 框架内部系统异常（本类**不**继承它）
- [DuckPhp\Ext\ThrowOnTrait](Ext-ThrowOnTrait.md) — `ThrowOn()` 的来源
- [DuckPhp\GlobalUser\UserException](GlobalUser-UserException.md) — 前台侧的对称异常
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 管理员组件
