# DuckPhp\Foundation\Business\Helper

## 简介

`Business\Helper` 是工程「业务层静态助手」的推荐实现：一个静态类，`use BusinessHelperTrait`。业务层通过 `Helper::Config(...)`、`Helper::Cache()`、`Helper::ValidatorValid(...)`、`Helper::AdminService()` 等调用业务所需的框架能力，而不直接接触组件类。

## 类信息

- 命名空间：`DuckPhp\Foundation\Business`
- 声明：`class Helper`
- 使用的 Trait：`DuckPhp\Helper\BusinessHelperTrait`

## 使用方式

```php
use DuckPhp\Foundation\Business\Helper;

$setting = Helper::Setting('app_name');
$rows    = Helper::Cache()->get('hot_list');
$errors  = Helper::ValidatorValid($input, ['age' => 'int|between:1,120']);
```

## 注意事项

- Helper 类不保存状态；方法全部来自 `BusinessHelperTrait`（再转发到 `App/Configer/Cache/GlobalEvent/Validator/GlobalAdmin/GlobalUser` 等）。
- 若需要同时使用 Controller/App 助手，可改用 `Foundation\Helper`（四合一）。

## 方法列表

本类方法全部由 `BusinessHelperTrait` 提供（16 个静态方法），签名与说明见 [Helper-BusinessHelperTrait](Helper-BusinessHelperTrait.md)。

## 相关链接

- [DuckPhp\Helper\BusinessHelperTrait](Helper-BusinessHelperTrait.md) — 方法来源
- [DuckPhp\Foundation\Business\Base](Foundation-Business-Base.md) — 业务基类
- [DuckPhp\Foundation\Helper](Foundation-Helper.md) — 四层合一的静态助手
