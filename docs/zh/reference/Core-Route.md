# DuckPhp\Core\Route

路由组件。

## 简介

`Route` 负责根据 URL 解析并调用对应的控制器和方法。它支持 URL 前缀、控制器后缀、方法前缀、路径扩展名、控制器类映射等配置，同时提供 URL 生成、资源 URL 生成和路由钩子扩展能力。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `namespace` | `''` | 应用命名空间前缀。 |
| `namespace_controller` | `'Controller'` | 控制器命名空间，相对于 `namespace`。 |
| `controller_path_ext` | `''` | 路径扩展名，例如 `.html`。设置后只有带此后缀的 URL 会匹配。 |
| `controller_welcome_class` | `'Main'` | 默认控制器类名，当 URL 为空时使用。 |
| `controller_welcome_class_visible` | `false` | 是否允许 URL 中显式出现欢迎控制器类名。 |
| `controller_welcome_method` | `'index'` | 默认方法名。 |
| `controller_class_adjust` | `''` | 控制器/方法名调整规则，例如 `uc_method;uc_class`。 |
| `controller_class_base` | `''` | 控制器基类约束，控制器必须继承该基类。支持 `~` 替换为控制器命名空间前缀。 |
| `controller_class_postfix` | `'Controller'` | 控制器类名后缀。 |
| `controller_method_prefix` | `'action_'` | 控制器方法前缀。 |
| `controller_prefix_post` | `'do_'` | POST 请求时额外追加的方法前缀。 |
| `controller_class_map` | `[]` | 控制器类映射，用于将某个类替换为另一个类。 |
| `controller_resource_prefix` | `''` | 静态资源前缀，用于生成资源 URL。 |
| `controller_url_prefix` | `''` | URL 路径前缀，例如 `api/`。 |
| `controller_fix_mistake_path_info` | `true` | 当 `PATH_INFO` 为空且脚本为 `/index.php` 时，从 `REQUEST_URI` 自动修复。 |

## 使用方式

### 启动路由

```php
use DuckPhp\Core\Route;

Route::RunQuickly([
    'namespace' => 'MyApp',
    'controller_path_ext' => '.html',
]);
```

### 获取路由参数

```php
use DuckPhp\Core\Route;

$all = Route::Parameter();          // 全部参数
$id = Route::Parameter('id', 0);     // 获取 id，默认值为 0
```

### URL 生成

```php
use DuckPhp\Core\Route;

$url = Route::Url('user/profile');     // 生成 /user/profile
$res = Route::Res('css/style.css');    // 生成静态资源 URL
$domain = Route::Domain();              // 当前域名，如 //example.com
$domainWithScheme = Route::Domain(true); // 当前域名，如 http://example.com
```

### 全局函数

```php
$url = __url('user/profile');
$res = __res('images/logo.png');
$domain = __domain();
```

### 绑定测试路径

```php
use DuckPhp\Core\Route;

Route::_()->bind('/user/profile', 'GET');
Route::_()->run();
```

### 路由钩子

```php
use DuckPhp\Core\Route;

Route::_()->addRouteHook(function ($path_info) {
    if ($path_info === '/special') {
        echo 'special route';
        return true; // 返回 true 表示已处理
    }
    return false;
}, 'append-outter');
```

## 路由匹配规则

默认路由规则：

- URL `/foo/bar` 映射到 `MyApp\Controller\FooController->action_bar()`。
- URL `/` 或空路径映射到 `MyApp\Controller\MainController->action_index()`。
- POST 请求时，如果存在 `action_do_bar` 方法，则优先调用该方法。

## 配置示例

### 基础配置

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'namespace' => 'MyApp',
        'namespace_controller' => 'Controller',
        'controller_path_ext' => '.html',
    ];
}
```

### URL 前缀

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'controller_url_prefix' => 'api',
        'namespace' => 'MyApp',
    ];
}
```

### 控制器映射

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'controller_class_map' => [
            'MyApp\Controller\OldController' => 'MyApp\Controller\NewController',
        ],
    ];
}
```

### 静态资源前缀

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'controller_resource_prefix' => 'https://cdn.example.com/',
    ];
}
```

## 注意事项

1. 控制器类名默认会加上 `controller_class_postfix` 后缀，方法名默认会加上 `controller_method_prefix` 前缀。
2. 控制器方法不能以下划线开头，不能是静态方法。
3. 如果设置了 `controller_class_base`，目标控制器必须继承该基类。
4. 路由钩子分为 `prepend-outter`、`prepend-inner`、`append-inner`、`append-outter` 四个位置，返回 `true` 表示已处理并终止后续流程。
5. 当路由失败时，可通过 `getRouteError()` 获取错误代码和原因。

## 全部选项

```php
    'namespace' => '',
    'namespace_controller' => 'Controller',

    'controller_path_ext' => '',
    'controller_welcome_class' => 'Main',
    'controller_welcome_class_visible' => false,
    'controller_welcome_method' => 'index',

    'controller_class_adjust' => '',
    'controller_class_base' => '',
    'controller_class_postfix' => 'Controller',
    'controller_method_prefix' => 'action_',
    'controller_prefix_post' => 'do_',

    'controller_class_map' => [],

    'controller_resource_prefix' => '',
    'controller_url_prefix' => '',
    'controller_fix_mistake_path_info' => true,
```

## 方法列表

### 公共方法

    public static function RunQuickly(array $options = [], callable $after_init = null)
快速初始化并运行路由

    public static function Route()
返回当前路由实例

    public static function Parameter($key = null, $default = null)
获取路由参数

    public function _Parameter($key = null, $default = null)
内部实现：获取全部或单个路由参数

    public function bind($path_info, $request_method = 'GET')
绑定指定路径和请求方法，常用于测试

    public function run()
执行路由流程，依次运行 pre hooks、默认路由、post hooks

    public function forceFail()
强制标记路由失败

    public function addRouteHook($callback, $position = 'append-outter', $once = true)
添加路由钩子，支持四个位置

    public function defaulToggleRouteCallback($enable = true)
启用或禁用默认路由回调

    public function defaultRunRouteCallback($path_info = null)
运行默认路由回调

    public function defaultGetRouteCallback($path_info)
获取默认路由回调，返回 `[对象, 方法名]` 或 `null`

    public function getControllerNamespacePrefix()
返回控制器命名空间前缀

    public function replaceController($old_class, $new_class)
在运行时替换控制器类映射

    public static function PathInfo($path_info = null)
获取或设置当前 `PATH_INFO`

    public static function Url($url = null)
生成 URL

    public static function Res($url = null)
生成资源 URL

    public static function Domain($use_scheme = false)
获取当前域名

    public function _Url($url = null)
内部实现：生成 URL

    public function _Res($url = null)
内部实现：生成资源 URL

    public function _Domain($use_scheme = false)
内部实现：获取当前域名

    public function setParameters($parameters)
设置路由参数

    public function getRouteError()
获取最后一次路由错误信息

    public function getRouteCallingPath()
获取当前路由匹配到的路径

    public function getRouteCallingClass()
获取当前路由匹配到的控制器类

    public function getRouteCallingMethod()
获取当前路由匹配到的方法名

    public function setRouteCallingMethod($calling_method)
设置当前路由匹配到的方法名

    public function dumpAllRouteHooksAsString()
以字符串形式导出所有路由钩子

    public function setUrlHandler($callback)
设置自定义 URL 处理器

    public function getUrlHandler()
获取当前 URL 处理器

### 受保护方法

    protected function getRunResult()
返回路由运行结果

    protected function pathToClassAndMethod($path_info)
将 URL 路径转换为控制器类名和方法名

    protected function adjustClassBaseName($path_info)
调整类基础名，处理欢迎控制器和路径块

    protected function doControllerClassAdjust($blocks, $method)
根据 `controller_class_adjust` 调整类名和方法名

    protected function getCallbackFromClassAndMethod($full_class, $method, $path_info)
反射获取控制器对象和方法

    protected function adjustMethod($method, $ref)
根据请求方法调整实际调用的方法名

    protected function getPathInfo()
获取当前 `PATH_INFO`，支持自动修复

    protected function setPathInfo($path_info)
设置 `PATH_INFO` 并同步到全局上下文

    protected function getUrlBasePath()
获取 URL 基础路径

## 相关链接

- [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md)
- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md)
- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md)
