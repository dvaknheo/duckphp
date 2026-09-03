# DuckPhp\GlobalAdmin\AdminException

## 简介

`AdminException` 是管理员（后台）领域的异常基类，继承框架系统异常 `DuckPhp\Core\DuckPhpSystemException`。管理员动作/服务实现抛出与后台相关的业务异常时，可使用本类（或其子类），便于上层按异常类型统一处理。

源码中该类为空类（`class AdminException extends DuckPhpSystemException { }`），本身不增加行为；所有能力继承自父类（含 `ThrowOn` 静态快捷抛异常等）。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`class AdminException extends DuckPhp\Core\DuckPhpSystemException`

## 使用方式

```php
use DuckPhp\GlobalAdmin\AdminException;

throw new AdminException('后台权限不足');

// 或用 DuckPhpSystemException 提供的静态快捷方式：
AdminException::ThrowOn(!$allowed, '后台权限不足', 403);
```

## 注意事项

- 继承链：`AdminException → DuckPhpSystemException → \Exception`，并通过 `ThrowOnTrait` 获得 `ThrowOn()` 静态方法。
- 框架的异常管理器会把“系统异常/项目异常”区分处理；后台异常建议用本类以便单独捕获（`exception_for_admin` 类映射可按工程需要配置）。

## 方法列表

本类为空类，未额外声明方法（继承父类与 PHP `\Exception` 的全部能力）。

## 相关链接

- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) — 父类
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 管理员组件
