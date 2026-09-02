# DuckPhp\Ext\RouteHookWebInstallerView

Web 安装器默认视图模板。

## 简介

`RouteHookWebInstallerView` 不是 PHP 类，而是 `DuckPhp\Ext\RouteHookWebInstaller` 使用的内置视图模板文件。它包含完整的 HTML 表单，用于在浏览器中完成项目首次安装时的环境检查、数据库配置、Redis 配置与安装执行。

该视图通过 `RouteHookWebInstaller` 的 `show()` 方法渲染，渲染前会 `extract($data)` 注入变量，因此模板中可直接使用 `$title`、`$checks`、`$installed`、`$post` 等变量。

## 选项

无。视图本身不定义配置选项，其行为由 `RouteHookWebInstaller` 的选项控制。

## 使用方式

### 默认视图

`RouteHookWebInstaller` 默认使用本视图文件渲染安装页面：

```php
use DuckPhp\Ext\RouteHookWebInstaller;

class App extends DuckPhp
{
    public $options = [
        'ext' => [
            RouteHookWebInstaller::class => true,
        ],
    ];
}
```

### 自定义视图

通过 `web_installer_view` 选项指定自定义视图类，或设置 `web_installer_view_block_custom` 修改安装表单区块。

## 注意事项

1. 模板中使用 `__hl()` 输出多语言文本，使用 `__h()` 输出 HTML 转义内容。
2. 表单提交后会回到当前 URL，`RouteHookWebInstaller` 负责处理 POST 数据。
3. 如需完全自定义界面，建议继承 `RouteHookWebInstaller` 并覆盖 `render()` 相关方法。

## 相关链接

- [中文参考手册目录](index.md)
- [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md)
