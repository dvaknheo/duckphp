# DuckPhp\Ext\RouteHookFunctionRoute

## 简介

`RouteHookFunctionRoute` 是“函数式路由”扩展：把当前 PATH_INFO 映射成**可调用函数/回调名**并执行，而不是走“控制器类 + 动作方法”的标准路由。默认挂在 `append-inner` 钩子位（默认路由失败后才尝试）。

映射规则：`PATH_INFO` 去掉前导 `/` 后把 `/` 换成 `_`，空串用 `index`；再拼上前缀（`function_route_method_prefix` 默认 `action_`）与 POST 时追加的 `controller_prefix_post`（如 `do_`），得到回调名；若 `is_callable` 则调用并命中。可配置 404 时回退到 `{prefix}index`。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class RouteHookFunctionRoute extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `function_route` | `false` | 是否启用（保留开关位）。 |
| `function_route_method_prefix` | `'action_'` | 回调名前缀。 |
| `function_route_404_to_index` | `false` | 未命中时是否回退调用 `{prefix}index`。 |

## 使用方式

```php
\DuckPhp\Ext\RouteHookFunctionRoute::_()->init([], $app);

// 请求 /hello/world（GET）→ 尝试调用回调 action_hello_world
// 请求 /hello/world（POST 且 controller_prefix_post='do_'）→ 先试 action_do_hello_world
function action_hello_world() { echo 'hello'; }
```

## 注意事项

- 回调名是否“可调用”以 `is_callable` 判定：可以是已定义函数、闭包或其它可调用体（需要提前注册）。
- `_Hook` 里先用 POST 参数决定是否加 `do_` 前缀；带 POST 未命中时还会再去掉 `do_` 试一次。
- 本钩子位于 `append-inner`：默认 MVC 路由不命中时才有机会执行（可作“函数路由兜底”）。

## 方法列表

### 公共方法

    public static function Hook($path_info)
静态钩子入口，转发 `_Hook`。

    public function _Hook($path_info = '/')
按 PATH_INFO 拼回调名并尝试调用；未命中按选项回退 `index`。

### 受保护方法

    protected function initContext(object $context): void
把 `Hook` 挂到 Route 的 `append-inner` 钩子位。

### 私有方法

    private function runCallback($callback)
若回调可调用则执行并返回 `true`。

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md) — 钩子宿主
- [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md) — 钩子列表管理
