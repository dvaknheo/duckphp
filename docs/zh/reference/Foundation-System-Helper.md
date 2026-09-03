# DuckPhp\Foundation\System\Helper

## 简介

`System\Helper` 是工程「System 层静态助手」的推荐实现：一个静态类，`use AppHelperTrait`。System 层（应用类、全局配置、系统级逻辑）通过它调用应用级能力：`Setting()`、`header()/exit()/session_*`、`addRouteHook()`、`FireGlobalEvent()`、`Redis()`、`getCliParameters()` 等。

## 类信息

- 命名空间：`DuckPhp\Foundation\System`
- 声明：`class Helper`
- 使用的 Trait：`DuckPhp\Helper\AppHelperTrait`

## 使用方式

```php
use DuckPhp\Foundation\System\Helper;

$debug = Helper::Setting('app.debug');
Helper::FireGlobalEvent('app_ready', $app);
```

## 注意事项

- Helper 类不保存状态；方法全部来自 `AppHelperTrait`（转发到 `ExceptionManager/Runtime/Route/View/DbManager/SuperGlobal/SystemWrapper/RedisManager/Console/GlobalEvent/ExtOptionsLoader` 等）。
- System 层是分层约定里“允许直接触达框架能力”的一层，因此放 App 级助手最合适。

## 方法列表

本类方法全部由 `AppHelperTrait` 提供（38 个静态方法），签名与说明见 [Helper-AppHelperTrait](Helper-AppHelperTrait.md)。

## 相关链接

- [DuckPhp\Helper\AppHelperTrait](Helper-AppHelperTrait.md) — 方法来源
- [DuckPhp\Foundation\Helper](Foundation-Helper.md) — 四层合一的静态助手
