# DuckPhp\Foundation\Controller\Helper

## 简介

`Controller\Helper` 是工程「控制器层静态助手」的推荐实现：一个静态类，`use ControllerHelperTrait`。控制器里写 `Helper::GET(...)`、`Helper::Show(...)` 时，实际调用的是该 Trait 提供的全部静态便捷方法（请求输入、URL、渲染、异常注册、用户/管理员查询等）。

工程中也可不新建类，直接用 `Foundation\Helper`（四层合一的静态助手）；两者能力等价，区别在命名空间分层。

## 类信息

- 命名空间：`DuckPhp\Foundation\Controller`
- 声明：`class Helper`
- 使用的 Trait：`DuckPhp\Helper\ControllerHelperTrait`

## 使用方式

```php
use DuckPhp\Foundation\Controller\Helper;

$name = Helper::POST('name');
if (Helper::IsAjax()) {
    Helper::ShowJson(['ok' => true]);
    return;
}
Helper::Show(get_defined_vars(), 'index');
```

## 注意事项

- Helper 类本身不保存状态，全部方法来自 Trait（Trait 内再转发到对应组件）。
- 本类使用 `ControllerHelperTrait` 的默认消歧版本；若你还需要 `Business/App` 助手，推荐改用 `Foundation\Helper`（它显式处理了 `Setting/Options/Config/XpCall/FireGlobalEvent/AdminService/UserService` 等冲突）。

## 方法列表

本类方法全部由 `ControllerHelperTrait` 提供（共 46 个静态方法），签名与说明见 [Helper-ControllerHelperTrait](Helper-ControllerHelperTrait.md)。

## 相关链接

- [DuckPhp\Helper\ControllerHelperTrait](Helper-ControllerHelperTrait.md) — 方法来源
- [DuckPhp\Foundation\Helper](Foundation-Helper.md) — 四层合一的静态助手
- [DuckPhp\Foundation\Controller\Base](Foundation-Controller-Base.md) — 控制器基类
