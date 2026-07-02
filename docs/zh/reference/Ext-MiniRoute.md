# DuckPhp\Ext\MiniRoute

迷你路由扩展组件。

## 简介

`MiniRoute` 是一个精简版路由实现，根据 `PATH_INFO` 将请求映射到 `Controller` 目录下的类与方法。它可以独立运行，也可以作为 `DuckPhp\Core\Route` 的替代方案。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `namespace` | `''` | 应用命名空间前缀。 |
| `namespace_controller` | `'Controller'` | 控制器子命名空间。 |
| `controller_path_ext` | `''` | 路径后缀，例如 `.html`。为空时不检查后缀。 |
| `controller_welcome_class` | `'Main'` | 默认入口控制器类名。 |
| `controller_welcome_class_visible` | `false` | 为 `false` 时，不允许直接访问 `Main` 控制器。 |
| `controller_welcome_method` | `'index'` | 默认入口方法名。 |
| `controller_class_postfix` | `''` | 控制器类名后缀。 |
| `controller_method_prefix` | `''` | 方法名前缀。 |
| `controller_class_map` | `[]` | 控制器类映射，用于替换旧类名为新类名。 |
| `controller_resource_prefix` | `''` | 静态资源 URL 前缀。为空时使用 `_Url()` 生成。 |
| `controller_url_prefix` | `''` | URL 路径前缀。 |

## 使用方式

### 独立运行

```php
$route = new \DuckPhp\Ext\MiniRoute();
$route->init([
    'namespace' => 'MyApp',
    'controller_welcome_class' => 'Main',
]);
$ok = $route->run();
if (!$ok) {
    echo '404: ' . $route->getRouteError();
}
```

### 作为 DuckPhp 扩展加载

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\MiniRoute::class => true,
        ],
        'controller_welcome_class' => 'Home',
    ];
}
```

### 获取 URL

```php
$url = MiniRoute::Url('user/profile'); // 生成相对 URL
$res = MiniRoute::Res('style.css');     // 生成资源 URL
$domain = MiniRoute::Domain();           // 当前域名
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\MiniRoute::class => true,
        ],
        'namespace' => 'MyApp',
        'namespace_controller' => 'Controller',
        'controller_welcome_class' => 'Main',
        'controller_url_prefix' => 'api',
        'controller_resource_prefix' => 'https://cdn.example.com/assets/',
    ];
}
```

## 注意事项

1. 路由解析基于 `$_SERVER['PATH_INFO']`，支持 `__SUPERGLOBAL_CONTEXT` 全局环境上下文。
2. 控制器类必须存在且可反射，方法不能以 `_` 开头，且不能是静态方法。
3. 如果设置了 `controller_path_ext`，路径必须匹配该后缀，否则返回错误。
4. `controller_url_prefix` 会作为 URL 前缀和基路径的一部分。
5. 错误码以 `E0xx` 形式记录，可通过 `getRouteError()` 获取。

## 全部选项

```php
    public $options = [
        'namespace' => '',
        'namespace_controller' => 'Controller',
        'controller_path_ext' => '',
        'controller_welcome_class' => 'Main',
        'controller_welcome_class_visible' => false,
        'controller_welcome_method' => 'index',
        'controller_class_postfix' => '',
        'controller_method_prefix' => '',
        'controller_class_map' => [],
        'controller_resource_prefix' => '',
        'controller_url_prefix' => '',
    ];
```

## 方法列表

### 公共方法

    public static function Route()
返回当前路由组件实例。

    public function run()
执行路由解析并调用匹配的控制器方法。成功返回 `true`，失败返回 `false`。

    public function defaultGetRouteCallback($path_info)
根据路径解析出类名和方法，返回回调数组 `[对象, 方法名]`。

    public function getControllerNamespacePrefix()
获取控制器命名空间前缀。

    public function replaceController($old_class, $new_class)
在映射表中替换控制器类名。

    public static function PathInfo($path_info = null)
获取或设置 `PATH_INFO`。

    public function _PathInfo($path_info = null)
`PathInfo()` 的内部实现。

    public function getRouteError()
获取最后一次路由错误信息。

    public function getRouteCallingPath()
获取当前路由调用路径。

    public function getRouteCallingClass()
获取当前路由调用的类名。

    public function getRouteCallingMethod()
获取当前路由调用的方法名。

    public function setRouteCallingMethod($calling_method)
设置当前路由调用的方法名。

    public static function Url($url = null)
生成 URL。

    public static function Res($url = null)
生成资源 URL。

    public static function Domain($use_scheme = false)
获取当前域名。

    public function _Url($url = null)
`Url()` 的内部实现。

    public function _Res($url = null)
`Res()` 的内部实现。

    public function _Domain($use_scheme = false)
`Domain()` 的内部实现。

### 受保护方法

    protected function pathToClassAndMethod($path_info)
将路径解析为类名和方法名。

    protected function getPathInfo()
从 `$_SERVER` 或全局环境上下文中读取 `PATH_INFO`。

    protected function setPathInfo($path_info)
设置 `PATH_INFO` 并同步到全局环境上下文。

    protected function getUrlBasePath()
计算 URL 基路径。

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\Route](Core-Route.md)
