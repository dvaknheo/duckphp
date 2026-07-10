# DuckPhp\Component\RouteHookRouteMap

路由钩子：路由映射。

## 简介

`RouteHookRouteMap` 组件允许通过配置将 URL 直接映射到回调函数或控制器方法。它支持精确匹配、通配符匹配和正则匹配，并且比默认的 PATH_INFO 路由更灵活。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。`route_map_important` 挂在 `prepend-inner` 位置，`route_map` 挂在 `append-outter` 位置。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `controller_url_prefix` | `''` | 控制器 URL 前缀。映射只对匹配该前缀的 URL 生效。 |
| `route_map_important` | `[]` | 高优先级路由映射。在其他路由匹配之前执行。 |
| `route_map` | `[]` | 普通路由映射。在默认 PATH_INFO 路由之后作为 fallback 执行。 |

## 路由映射格式

`route_map` 和 `route_map_important` 都是关联数组，键是 URL 模式，值是回调：

```php
class App extends DuckPhp
{
    public $options = [
        'route_map' => [
            'hello' => 'HelloController@index',
            'user/{id:\d+}' => 'UserController@detail',
            'api/*' => 'ApiController@handle',
            '~^test/(\d+)$~x' => 'TestController@show',
        ],
    ];
}
```

### 模式类型

| 模式前缀 | 说明 | 示例 |
|---|---|---|
| 无 / 或普通字符串 | 精确匹配路径。 | `'hello' => 'HelloController@index'` |
| `/` | 精确匹配路径，与无前缀等效。 | `'/hello' => 'HelloController@index'` |
| `@` | 占位符模式，支持 `{name}` 和 `{name:regex}`。 | `'@user/{id:\d+}' => 'UserController@detail'` |
| `~` | 完整正则表达式模式。 | `'~^test/(\d+)$~x' => 'TestController@show'` |
| 末尾 `*` | 通配符匹配，捕获剩余路径作为参数。 | `'api/*' => 'ApiController@handle'` |

### 回调格式

| 回调形式 | 说明 | 示例 |
|---|---|---|
| `'ClassName@method'` | 调用可变单例 `ClassName::_()` 的 `method` 方法。 | `'UserController@index'` |
| `'ClassName->method'` | 创建新实例并调用 `method` 方法。 | `'UserController->index'` |
| 可调用数组 | 直接调用。 | `['ClassName', 'method']` 或 `[$obj, 'method']` |
| 闭包 | 直接执行。 | `function () { ... }` |

### 控制器命名空间替换

如果回调以 `~` 开头，会自动替换为当前控制器命名空间前缀：

```php
'route_map' => [
    '@user/{id}' => '~UserController@detail',
]
// 实际映射为：@user/{id} => App\Controller\UserController@detail
```

## 运行时添加映射

通过 `assignRoute()` 和 `assignImportantRoute()` 可以在运行时添加映射：

```php
use DuckPhp\Component\RouteHookRouteMap;

RouteHookRouteMap::_()->assignRoute('new/page', 'NewController@index');
RouteHookRouteMap::_()->assignImportantRoute('admin/login', 'AdminController@login');
```

## 获取当前映射

```php
$maps = RouteHookRouteMap::_()->getRouteMaps();
// 返回 ['route_map_important' => [...], 'route_map' => [...]]
```

## 参数传递

占位符和正则匹配捕获的参数会通过 `Route::_()->setParameters()` 设置，在控制器中可以通过参数获取：

```php
class UserController
{
    public function detail($id)
    {
        // $id 来自路由映射中的 {id} 占位符
    }
}
```

## 注意事项

1. `route_map_important` 在 `prepend-inner` 位置执行，优先于默认 PATH_INFO 路由。
2. `route_map` 在 `append-outter` 位置执行，作为默认路由未匹配时的 fallback。
3. 同一映射中的占位符和正则会被自动编译为完整的正则表达式。
4. 如果映射的回调指向控制器类，需要确保类能被自动加载。
5. 映射匹配的 URL 会先去除 `controller_url_prefix` 前缀。

## 全部选项

```php
public $options = [
    'controller_url_prefix' => '',
    'route_map_important' => [],
    'route_map' => [],
];
```

## 方法列表

### 公共方法

    public static function PrependHook($path_info)
高优先级路由钩子入口，处理 `route_map_important`

    public static function AppendHook($path_info)
普通路由钩子入口，处理 `route_map`

    public function compile(string $pattern_url, array $rules = []): string
将占位符模式编译为完整正则表达式

    public function assignRoute($key, $value = null)
添加普通路由映射

    public function assignImportantRoute($key, $value = null)
添加高优先级路由映射

    public function getRouteMaps()
获取当前所有路由映射

    public function doHook($path_info, $is_append)
根据 `$is_append` 选择处理 `route_map` 或 `route_map_important`

### 受保护方法

    protected function initContext(object $context): void
注册 `prepend-inner` 和 `append-outter` 路由钩子

    protected function compileMap(array $map, string $namespace_controller): array
编译整个路由映射表，替换控制器命名空间前缀

    protected function matchRoute(string $pattern_url, string $path_info, &$parameters): bool
根据模式匹配 URL，填充参数

    protected function getRouteHandelByMap(array $routeMap, string $path_info)
在映射表中查找匹配的回调

    protected function adjustCallback($callback, array $parameters)
调整回调形式，设置调用方法，返回可调用的回调

    protected function doHookByMap(string $path_info, array $route_map): bool
执行匹配到的回调

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md)
- [DuckPhp\Helper\AppHelperTrait](Helper-AppHelperTrait.md)
