# DuckPhp\Ext\RouteHookWebInstallerView

## 简介

`RouteHookWebInstallerView` 是 `RouteHookWebInstaller` 的**内置安装视图**。注意：该文件**不定义任何 PHP 类/函数**，而是一份混排 HTML 与 PHP 的视图模板，由 `RouteHookWebInstaller` 在 `show()` 时 `extract($data)` 后引入渲染（见文件头注释）。

页面职责：

- 已安装（`$installed` 非空）：显示“安装完成”与 5 秒后跳转首页；
- 未安装：显示环境检查表（`$checks`）、数据库/Redis 配置表单（含 `controller_resource_prefix` 提示）、`web_installer_force` 复选与自定义区块。

所有 UI 文案均经 `__hl('webinstaller.*')` 取多语言句（默认句来自 `RouteHookWebInstaller::builtin_default_sentences`，可以用 `config/lang-{locale}-for_webinstaller.php` 或主语言文件覆盖）。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：无（纯视图文件，无 class/interface/trait）

## 使用方式

一般不需要直接引用本文件：配置 `RouteHookWebInstaller`（`web_installer_view` 留空）即使用内置视图。

```php
// App 选项示例：
'ext' => [\DuckPhp\Ext\RouteHookWebInstaller::class => true],
```

需要定制外观时，把内置文件拷出修改后经 `web_installer_view` 指定；如需在表单里追加自定义字段，用 `web_installer_view_block_custom`。

## 注意事项

- 视图数据由宿主注入：`$title`、`$installed`、`$checks`、`$controller_resource_prefix`、各配置表项等；请勿在本文件内直接触碰框架 API。
- 文案键统一 `webinstaller.*`；给 `web_installer_default_sentences` 传数组可覆盖默认英文句。
- 文件采用“控制结构用花括号、HTML 保留自身缩进”的写法（见头注释约定）。

## 方法列表

本文件为视图模板，无任何方法。

## 相关链接

- [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) — 宿主（负责数据与渲染）
- [DuckPhp\Component\Lang](Component-Lang.md) — `__hl()` 多语言来源
