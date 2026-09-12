# DuckPhp\Component\RouteHookRouteMap

路由地图钩子：允许把某些 URL（精确、持 `*`、或 `^…$)正则）直接跳到特定“回调/控制器”处理，分“重要（route_map_important，前置在内）”与“普通（route_map，后置收尾）”两组。

## 简介

`RouteHookRouteMap extends ComponentBase` 通过两个 `Route` hook：

- `prepend-inner`.(PrependHook)：先跑 `route_map_important`；
- `append-outter`.(AppendHook)：默认解析没命中后再试 `route_map`。

地图写法（options `route_map`/`route_map_important` 为 `模式→ 目标`）：

模式可：
- 固定全路径（以 `/` 开头精确比较，可去 `controller_url_prefix`）；
- `^…$`（完整正则，`x` 修饰可以带 `#…` 尾注释扩）；
- 以 `@` 开头（会从 `{name(:regex)?}` 片段包被编译成正则 命名捕获——由 `compile()` 处理）；
- 尾部 `*`＝前缀匹配，把余下部分作为 para 塞给 `Route::Parameter`。

目标（callback）可 `Class@method`（单例 `_()`）、`Class->method`（new）、真回调调 `setParameters` 并执行。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class RouteHookRouteMap extends ComponentBase`
- 目标：改由地图立即处理 URL（默认 Route 被短路）。

## 选项

`RouteHookRouteMap::$options`：

| 选项 | 默认 | 说明 |
|---|---|---|
| `controller_url_prefix` | `''` | 匹配前可统一去掉 url 前缀。 |
| `route_map_important` | `[]` | 重要组（先探）。 |
| `route_map` | `[]` | 普通组（后探）。 |

运行时也可动态追加 `assignRoute`/`assignImportantRoute`；`getRouteMaps()` 读回两份。

## 使用方式

```php
RouteHookRouteMap::_()->init([
  'route_map_important' => [
     '/login' => 'Public@action_login',
     '@page/{id:\d+}'  => 'Post@showPage',
  ],
  'route_map' => [
     '/go/*'   => 'Legacy@forward',   // * 段进 Parameter
  ],
], App::_());
```

## 说明/细节

- 逻辑顺序：prepend → important map → “默认路由” → append → normal map（因此 normal 作为兜底）。
- compileMap 会把 callback 中 `~` 换成 controller 命名空间（便于写 `~Post@…`）。
- 命中后 `($callback)()`（若有 callable real），对于字符串 `Class@method` / `Class->method` 解析并将其 method 放 `${attributes} CallingMethod`。
- matchRoute 还支持指定 (regex) 与 wild*。

## 方法列表

### 公共方法

    public static function PrependHook($path_info)
静态壳：转发实例 `doHook($path_info, false)`（prepend 组）。

    public static function AppendHook($path_info)
静态壳：转发实例 `doHook($path_info, true)`（append 组）。

    public function compile(string $pattern_url, array $rules = []): string
把 `{name(:regex)?(optional?)}` 形式的模式编译为完整正则（`~^…$ #comment~x`）。

    public function assignRoute($key, $value = null)
动态追加 route 映射（支持数组批量）。

    public function assignImportantRoute($key, $value = null)
动态追加 important 路由映射（优先级更高）。

    public function getRouteMaps()
返回两组（普通/重要）当前映射配置。

    public function doHook($path_info, $is_append)
主入口：按 `$is_append` 选择地图组并尝试匹配（命中则调用并返回 `true`）。

### 受保护方法

    protected function initContext(object $context): void
挂入 `prepend-inner` + `append-outter` 两个钩子位。

    protected function compileMap(array $map, string $namespace_controller): array
地图预处理：`@` 编译 / `~namespace` 展开。

    protected function matchRoute(string $pattern_url, string $path_info, &$parameters): bool
模式匹配器：固定串 / `^` 正则 / `*` 通配，并导出参数。

    protected function getRouteHandelByMap(array $routeMap, string $path_info)
遍历某组映射，取首个命中模式并返回其 target。

    protected function adjustCallback($callback, array $parameters)
解析 `@`/`->`/callable 回调并写入 Route 参数、返回可调用目标。

    protected function doHookByMap(string $path_info, array $route_map): bool
用给定 map 找 handler，命中则调用并返回 `true`。

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md)
- 与 rewrite/resource 一样是路由钩子族；Lister 列出它地址上的映射见 Component-RouteLister。
