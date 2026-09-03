# DuckPhp\Foundation\Helper

## 简介

`Foundation\Helper` 是工程里「四层合一的静态助手」推荐实现：它同时组合 `ModelHelperTrait`、`BusinessHelperTrait`、`ControllerHelperTrait`、`AppHelperTrait`，并用 `insteadof` 显式解决四个 Trait 之间的同名方法冲突（与 `DuckPhpAllInOne` 的组合方式一致）。

工程代码可以只 `use DuckPhp\Foundation\Helper;` 一处引入，之后 Controller/Business/Model/System 各处都能调用对应能力；也可按分层各用各的 `*Helper` 类。

## 类信息

- 命名空间：`DuckPhp\Foundation`
- 声明：`class Helper`
- 使用的 Trait：`ModelHelperTrait`、`BusinessHelperTrait`、`ControllerHelperTrait`、`AppHelperTrait`

### 冲突消解规则（insteadof）

- **Business 优先**（`BusinessHelperTrait` insteadof `ControllerHelperTrait`/`AppHelperTrait`）：`Setting`、`Options`、`Config`、`XpCall`、`FireGlobalEvent`、`OnGlobalEvent`、`PathOfProject`、`PathOfRuntime`。
- **Controller 优先**（insteadof `AppHelperTrait`）：`header`、`setcookie`、`exit`。
- **Controller 优先**（insteadof `BusinessHelperTrait`）：`AdminService`、`UserService`。

## 使用方式

```php
use DuckPhp\Foundation\Helper;

Helper::Setting('app_name');      // Business/App 语义：App::_()->_Setting
Helper::DbForRead()->fetch(...);  // Model 助手
Helper::POST('name');             // Controller 助手（SuperGlobal）
Helper::exit();                   // Controller 语义（SystemWrapper 可替换 exit）
```

## 注意事项

- 本类与 `DuckPhpAllInOne` 采用同一套冲突消解：`Setting/Options/Config/XpCall/FireGlobalEvent/OnGlobalEvent/PathOfProject/PathOfRuntime` 取 Business 版；`header/setcookie/exit` 取 Controller 版；`AdminService/UserService` 取 Controller 版。
- 具体每个方法的签名与行为，见四个 `Helper-*Trait` 文档；本类不再重复方法节。

## 方法列表

本类方法全部由四个 Helper Trait 提供（冲突已消解），分别见：
- [Helper-AppHelperTrait](Helper-AppHelperTrait.md)
- [Helper-ControllerHelperTrait](Helper-ControllerHelperTrait.md)
- [Helper-BusinessHelperTrait](Helper-BusinessHelperTrait.md)
- [Helper-ModelHelperTrait](Helper-ModelHelperTrait.md)

## 相关链接

- [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) — 同类组合的“单类应用”版本
- 分层 Helper：`Controller\Helper` / `Business\Helper` / `Model\Helper` / `System\Helper`
