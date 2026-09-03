# DuckPhp\Ext\MyMiddlewareManager

## 简介

`MyMiddlewareManager` 是中间件管理器扩展：把 `options['middleware']` 里声明的中间件串成洋葱式调用链，最内层执行真正的路由（`Route::defaultRunRouteCallback`），执行完再触发 `onPostMiddleware()` 收尾。

初始化时会自动把 `Hook` 挂到 `RouteHookManager` 的 pre-run 钩子上（`attachPreRun()->append([static::class,'Hook'])`），使每次路由前都先跑中间件链。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class MyMiddlewareManager extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `middleware` | `[]` | 中间件列表（自外向内执行）。每项可为 callable，或字符串 `Class@method`（`_()` 单例）/ `Class->method`（`new`）。 |

## 使用方式

```php
\DuckPhp\Ext\MyMiddlewareManager::_()->init([
    'middleware' => [
        AuthMiddleware::class.'@handle',   // 鉴权
        LogMiddleware::class.'->handle',   // 日志
    ],
], $app);
// 之后每次路由请求前按 鉴权 → 日志 → 路由 顺序执行
```

## 注意事项

- `doHook()`：把 `middleware` 反转后用 `array_reduce` 构建嵌套回调（洋葱模型），最内层是 `runSelfMiddleware()`（真正跑默认路由并记录 `defaultResult`）；链跑完调用 `onPostMiddleware()`，最后返回 `defaultResult`。
- `getRequest()`/`getResponse()` 目前返回空 `\stdClass`/空串，子类可覆盖以实现请求/响应对象传递。
- 中间件返回真值与否由业务决定；`Hook` 的返回值会成为路由钩子的“命中”判定（具体由钩子位置语义决定）。

## 方法列表

### 公共方法

    public function __construct()
初始化空 `request`/`response` 对象。

    public static function Hook($path_info)
静态钩子入口，转发 `doHook`。

    public function doHook($path_info = '')
执行中间件链：解析各中间件、洋葱式嵌套，最内层跑默认路由。

### 受保护方法

    protected function initContext(object $context): void
把 `Hook` 追加到 `RouteHookManager` 的 pre-run 钩子。

    protected function runSelfMiddleware(): string
最内层：执行 `Route::defaultRunRouteCallback()` 并记录 `defaultResult`。

    protected function onPostMiddleware(): void
中间件链结束后回调（子类可覆盖做收尾）。

    protected function getResponse(): string
取响应（基类返回空串）。

    protected function getRequest()
取请求对象（基类返回 `$this->request`）。

## 相关链接

- [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md) — 钩子挂载点
- [DuckPhp\Core\Route](Core-Route.md) — 默认路由回调来源
