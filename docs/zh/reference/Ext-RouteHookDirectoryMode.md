# DuckPhp\Ext\RouteHookDirectoryMode

目录模式路由钩子扩展。

## 简介

`RouteHookDirectoryMode` 用于支持以物理目录方式组织入口文件。例如 `/user/profile.php` 形式的 URL 会被解析为对应的控制器路径，并调整 `PATH_INFO` 供路由使用。同时它还会替换 URL 生成器，使生成的链接符合目录模式。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `mode_dir_basepath` | `''` | 物理目录基路径。URL 会基于该目录进行解析。 |

## 使用方式

### 作为 DuckPhp 扩展加载

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\RouteHookDirectoryMode::class => true,
        ],
        'mode_dir_basepath' => '/var/www/html',
    ];
}
```

### 自动解析路径

访问 `/user/profile.php/foo/bar` 时，钩子会：

1. 根据 `mode_dir_basepath` 和 `REQUEST_URI` 计算路径。
2. 遇到 `.php` 文件时，将其作为入口，并将后续段作为 `PATH_INFO`。
3. 设置 `Route` 的 `PATH_INFO` 供后续路由处理。

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\RouteHookDirectoryMode::class => true,
        ],
        'mode_dir_basepath' => __DIR__,
    ];
}
```

## 注意事项

1. 初始化时会注册 `prepend-outter` 路由钩子，并替换 `Route` 的 URL 处理器。
2. 如果 URL 为空或找不到对应 `.php` 文件，URL 生成器会返回原始 URL。
3. `__SUPERGLOBAL_CONTEXT` 全局环境上下文会被优先使用。

## 全部选项

```php
    public $options = [
        'mode_dir_basepath' => '',
    ];
```

## 方法列表

### 公共方法

    public static function Url($url = null)
目录模式下的 URL 生成器入口。

    public function onUrl($url = null)
根据物理目录结构生成对应的 URL。

    public static function Hook($path_info)
路由钩子入口。

    public function _Hook($path_info)
调整 `PATH_INFO` 并返回 `false` 以让后续路由继续处理。

### 受保护方法

    protected function initOptions(array $options)
保存 `mode_dir_basepath` 到实例属性。

    protected function initContext(object $context)
注册路由钩子和 URL 处理器。

    protected function adjustPathinfo($basepath, $path_info)
根据物理目录和 `REQUEST_URI` 重新计算 `PATH_INFO`。

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\Route](Core-Route.md)
