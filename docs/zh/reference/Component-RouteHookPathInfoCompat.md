# DuckPhp\Component\RouteHookPathInfoCompat

路由钩子：PATH_INFO 兼容模式。

## 简介

`RouteHookPathInfoCompat` 提供了一种兼容不支持 PATH_INFO 的服务器环境的路由方案。它通过 URL 参数或查询字符串参数来模拟 PATH_INFO，使 DuckPHP 能够在共享主机、Nginx 未配置 PATH_INFO 等环境下工作。

该组件默认不加载，需要在 `DuckPhp\DuckPhp` 的 `ext` 选项中手动启用，或在 `DuckPhp` 初始化时通过条件分支启用。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path_info_compact_enable` | `true` | 是否启用兼容模式。 |
| `path_info_compact_action_key` | `'_r'` | 指定 action 的 URL 参数名。 |
| `path_info_compact_class_key` | `''` | 指定 controller 类路径的 URL 参数名。为空时，action 参数直接包含完整路径。 |

## 两种兼容模式

### 单参数模式（默认）

`path_info_compact_class_key` 为空时，使用单个参数指定完整路径：

```php
class App extends DuckPhp
{
    public $options = [
        'path_info_compact_action_key' => '_r',
        'path_info_compact_class_key' => '',
    ];
}
```

URL 示例：

- `/index.php?_r=hello/index` → 路由到 `HelloController::index()`
- `/index.php?_r=admin/user/edit` → 路由到 `Admin\UserController::edit()`

### 双参数模式

`path_info_compact_class_key` 非空时，分别指定 controller 路径和 action：

```php
class App extends DuckPhp
{
    public $options = [
        'path_info_compact_action_key' => 'a',
        'path_info_compact_class_key' => 'm',
    ];
}
```

URL 示例：

- `/index.php?m=hello&a=index` → 路由到 `HelloController::index()`
- `/index.php?m=admin/user&a=edit` → 路由到 `Admin\UserController::edit()`

## URL 生成

`RouteHookPathInfoCompat` 同时注册了一个 URL 处理器，因此 `__url()` 函数会自动生成兼容模式的 URL：

```php
// path_info_compact_class_key = '' 时
__url('hello/index');
// 生成：/index.php?_r=hello%2Findex

// path_info_compact_class_key = 'm' 时
__url('hello/index');
// 生成：/index.php?m=hello&a=index
```

## 启用方式

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Component\RouteHookPathInfoCompat::class => true,
        ],
    ];
}
```

或者在运行环境检测后启用：

```php
if (!isset($_SERVER['PATH_INFO'])) {
    RouteHookPathInfoCompat::_()->init($this->options, $this);
}
```

## 注意事项

1. 该组件仅在不支持 PATH_INFO 的环境中需要启用。
2. 启用后会替换默认的 URL 处理器，影响 `__url()` 的输出格式。
3. 该钩子挂在 `prepend-outter` 位置，在路由匹配之前执行。
4. 路径中会自动处理 `index.php` 入口文件，避免生成的 URL 中出现重复的入口文件名。

## 全部选项

```php
public $options = [
    'path_info_compact_enable' => true,
    'path_info_compact_action_key' => '_r',
    'path_info_compact_class_key' => '',
];
```

## 方法列表

### 公共方法

    public static function Url($url = null)
URL 处理器入口，内部调用 `onUrl()`

    public function onUrl(?string $url = null): string
生成兼容模式的 URL

    public static function Hook($path_info)
路由钩子入口，内部调用 `_Hook()`

    public function _Hook($path_info)
将 URL 参数转换为 PATH_INFO，设置到路由组件中

### 受保护方法

    protected function initContext(object $context): void
注册路由钩子和 URL 处理器

    protected function filteRewrite(string $url, &$ret = false): ?string
可扩展的 URL 重写过滤接口（当前默认关闭）

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md)
- [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md)
