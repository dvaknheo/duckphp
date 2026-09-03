# DuckPhp\Ext\RouteHookDirectoryMode

## 简介

`RouteHookDirectoryMode` 实现“目录/文件模式”路由：让 URL 按“文档根下真实 .php 文件”来定位控制器（`/Foo/Bar.php/act` → 控制器 `Foo/Bar`、动作 `act`），而不是纯 PATH_INFO 命名空间映射。它挂在 `prepend-outter`，并把 URL 生成也接管（`setUrlHandler`），使 `__url()` 输出带 `.php` 的路径。

适用：以 PHP 文件为入口、目录即命名空间的传统站点风格。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class RouteHookDirectoryMode extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `mode_dir_basepath` | `''` | 站点根目录（用于把文件路径折算成控制器路径）。 |

## 使用方式

```php
\DuckPhp\Ext\RouteHookDirectoryMode::_()->init([
    'mode_dir_basepath' => __DIR__ . '/',
], $app);

// 请求 /admin/User.php/edit → 折算 PATH_INFO 为 admin/User/edit 之类
// 之后 __url('admin/User/edit') 会生成 /admin/User.php/edit
```

## 注意事项

- `adjustPathinfo()`：以 `DOCUMENT_ROOT + REQUEST_URI` 减去 `basepath` 得到路径；遇到 `.php` 段即视为控制器文件（去掉 `.php`），其后若没有更多段补 `index`。
- `onUrl()`（作为 URL handler）：根据 URL 路径尝试匹配真实存在的 `.php` 文件，命中则生成 `base_url + 类路径.php[/动作]` 的新 URL，并保留原 query 参数。
- `_Hook` 把折算后的 PATH_INFO 写回 `Route::PathInfo()` 并返回 `false`（继续后续路由处理）。

## 方法列表

### 公共方法

    public static function Url($url = null)
静态 URL 生成入口，转发 `onUrl`。

    public function onUrl(?string $url = null): ?string
把应用 URL 折算成“目录模式”URL（带 `.php`、匹配真实文件）。

    public static function Hook($path_info)
静态钩子入口，转发 `_Hook`。

    public function _Hook(string $path_info): bool
折算 PATH_INFO（目录/文件 → 控制器路径）后写回 Route，返回 `false`。

### 受保护方法

    protected function initOptions(array $options): void
读取 `mode_dir_basepath`。

    protected function initContext(object $context): void
把 `Hook` 挂到 `prepend-outter`，并把 `Url` 设为 Route 的 URL handler。

    protected function adjustPathinfo(string $basepath, string $path_info): string
把请求 URL 折算成控制器风格 PATH_INFO（处理 `.php` 段与 index 补全）。

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md) — 钩子与 URL 生成宿主
