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

（分 host / tool / 规则段参见源码顺序）

    public static PrependHook($path_info)          → 实例 doHook($path,false)
    public static AppendHook($path_info)           → 实例 doHook($path,true)

    protected initContext(object $context): void
    挂入 prepend-inner + append-outter。

    public compile(string $pattern_url, array $rules=[]): string
    把 `{name(:regex)?(optional?)}` 编译回 完整正则（`~^…$ # comment~x`）。

    public assignRoute($key,$value) / assignImportantRoute($key,$value)
    动态追加 route/important 地图（也支持数组）。

    public getRouteMaps()
    返回两组的当前配置。

    protected compileMap(array $map,string $namesapceCtl): array
    对地图预处理 `@` 编译 / `~namespace` 展开。

    protected matchRoute(string $pattern,string $path,&$params): bool
    固定 / ^正则 / * 的匹配器。

    protected getRouteHandelByMap(array $routeMap,string $path)
    遍历某组取首个命中匹配的模式返回调 target。

    protected adjustCallback($callback,array $parameters)
    解析 `@`/`->`/callable，写入 Route 参数与方法 call。

    protected doHookByMap(string $path,array $map): bool
    用该 map 找 handler 命中则调用并 return true。

    public doHook($path_info,$is_append)
    主要入口（is_append 决定地图组与是否允许普通行）。返回 true/false 让 Route 上下文。

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md)
- 与 rewrite/resource 一样是路由钩子族；Lister 列出它地址上的映射见 Component-RouteLister。
