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

中间件里短路（不调 `$next`，直接 `return` 一个响应）：

```php
function ($request, \Closure $next) {
    if (!is_logged_in()) {
        return '请先登录';      // 管理器输出它并返回 true ⇒ 控制器不再执行
    }
    return $next($request);    // 放行
}
```

## 注意事项

- `doHook()`：把 `middleware` 反转后用 `array_reduce` 构建嵌套回调（洋葱模型），最内层是 `runSelfMiddleware()`（真正跑默认路由并记录 `defaultResult`，本轮会被标记为「内层跑过」）；链跑完调用 `onPostMiddleware()`，然后：
  - **有中间件短路**（没调 `$next`）**且返回了响应**（`null`/`false` 不算响应）→ 经 `outputResponse()` 把响应发出去，`doHook()` 返回 `true` ⇒ `Route::run()` 认定「请求已处理」，**控制器不再执行**；
  - 其余情况返回 `defaultResult`（内层默认回调的结果）。
- 短路响应按字符串处理（`echo`）；要交给别的响应对象就覆盖 `outputResponse()`。**内层跑过之后中间件的返回值不参与输出**——控制器是边跑边 `echo` 的，那时内容已经发出去了（想加工响应得自己在中间件里用输出缓冲）。
- 没调 `$next` 却返回 `null`/`false`：视为「没处理」，保持老行为（`Route::run()` 仍会跑默认路由回调）。忘了调 `$next` 的中间件因此不会变成白屏。
- `getRequest()`/`getResponse()` 目前返回空 `\stdClass`/空串，子类可覆盖以实现请求/响应对象传递。
- `Hook` 的返回值就是 `doHook()` 的返回值，也就是路由钩子的「命中」判定。

## 方法列表

### 公共方法

    public function __construct()
初始化空 `request`/`response` 对象。

    public static function Hook($path_info)
静态钩子入口，转发 `doHook`。

    public function doHook($path_info = '')
执行中间件链：解析各中间件、洋葱式嵌套，最内层跑默认路由；有中间件短路（没调 `$next` 且返回了响应）时输出该响应并返回 `true`，否则返回 `defaultResult`。

### 受保护方法

    protected function initContext(object $context): void
把 `Hook` 追加到 `RouteHookManager` 的 pre-run 钩子。

    protected function runSelfMiddleware(): string
最内层：执行 `Route::defaultRunRouteCallback()` 并记录 `defaultResult`（「内层跑过」的标记由 `doHook()` 的内层闭包打，覆盖本方法不会丢）。

    protected function isHandledResponse($response): bool
短路响应的判定：`null`/`false` = 没处理（放行给默认路由），其余（含空串、`true`）= 已处理。

    protected function outputResponse($response): void
把短路响应发出去：基类只对非空字符串 `echo`，子类可覆盖（例如喂给自己的响应对象）。

    protected function onPostMiddleware(): void
中间件链结束后回调（子类可覆盖做收尾）。

    protected function getResponse(): string
取响应（基类返回空串）。

    protected function getRequest()
取请求对象（基类返回 `$this->request`）。

## 相关链接

- [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md) — 钩子挂载点
- [DuckPhp\Core\Route](Core-Route.md) — 默认路由回调来源
