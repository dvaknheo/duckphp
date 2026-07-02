# DuckPhp\Ext\MyMiddlewareManager

> ⚠️ 警告：该扩展是实验性的或已废弃，不建议在新项目中使用。

## 简介

`MyMiddlewareManager` 是一个中间件管理扩展。它允许在路由运行前后以洋葱圈方式执行一组中间件，但实现较简单，且与框架核心路由集成紧密，属于实验性实现。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `middleware` | `[]` | 中间件列表。每个中间件可以是可调用对象或 `Class@method` / `Class->method` 字符串。 |

## 使用方式

### 基础配置

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            DuckPhp\Ext\MyMiddlewareManager::class => true,
        ],
        'middleware' => [
            function ($request, $next) {
                // 前置处理
                $response = $next();
                // 后置处理
                return $response;
            },
        ],
    ];
}
```

### 使用字符串中间件

```php
'middleware' => [
    'Middleware\LogMiddleware@handle', // 调用 LogMiddleware::_()->handle($request, $next)
    'Middleware\AuthMiddleware->handle', // 调用 (new AuthMiddleware())->handle($request, $next)
]
```

### 中间件签名

中间件接收请求对象和 `$next` 回调，返回响应：

```php
function ($request, $next) {
    $response = $next();
    return $response;
}
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            DuckPhp\Ext\MyMiddlewareManager::class => true,
        ],
        'middleware' => [
            App\Middleware\AuthMiddleware::class . '@handle',
            App\Middleware\LogMiddleware::class . '@handle',
        ],
    ];
}
```

## 注意事项

1. 中间件通过 `RouteHookManager` 在路由运行前附加，执行顺序由数组反转后构造的洋葱圈决定。
2. 最内层调用为 `Route::_()->defaultRunRouteCallback()`，其结果会作为 `$next()` 的返回值。
3. 中间件执行完成后会调用 `onPostMiddleware()`，默认无操作，可被子类重写。
4. `request` 和 `response` 属性是 `stdClass` 对象，可在中间件中用于传递数据。
5. 该扩展是实验性的，中间件机制可能不完善。

## 全部选项

        'middleware' => [],

## 方法列表

### 公共方法

    public function __construct()
初始化请求和响应对象。

    public static function Hook($path_info)
路由钩子入口，调用当前实例的 `doHook()`。

    public function doHook($path_info = '')
执行中间件链。构造洋葱圈调用并返回默认结果。

### 受保护方法

    protected function initContext(object $context)
初始化上下文，将 `Hook` 方法附加到 `RouteHookManager` 的 pre-run 阶段。

    protected function runSelfMiddleware()
运行核心路由回调，获取默认结果并更新响应。

    protected function onPostMiddleware()
中间件执行后的钩子，默认空实现。

    protected function getResponse()
获取响应对象，默认返回空字符串。

    protected function getRequest()
获取请求对象。

## 相关链接

- [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md)
- [DuckPhp\Core\Route](Core-Route.md)
