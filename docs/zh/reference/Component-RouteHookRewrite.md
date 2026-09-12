# DuckPhp\Component\RouteHookRewrite

给 Route 加“重写地图（rewrite_map）”的能力：通过一个（前端好看 url → 内部 path）列表，在默认路由前把传入的 path 改写/重定到内部形式。

## 简介

`RouteHookRewrite extends ComponentBase` 主要服务“前端优雅 URL + 真正内部 path 分离”：配置诸如

```php
'rewrite_map' => [
  '/news'         => '/controller/news/',
  '/user/@[0-9]+'? => ...   // (用 `~` 前缀表示正则模版)
]
```

行为：

- `init()` 使其以 `prepend-outter` 挂一张 rewrite hook（还支持 `RouteHookDirectoryMode` 复用 `filteRewrite`）；
- hook 时把 `path_info + query` 作为输入 url，跑 `filteRewrite`；
  - 精确匹配：template == path（去前缀不加处理），命中后换到目标（新 query 合并）；
  - `~regex` 模板：视作正则整体替换输入 path 得到新 URL（`$`/编号可用）——都遵循上述合并查询参数。
- 命中后 `changeRouteUrl()`（保存原 GET，放新 query 到上下文/全局）并把 `PathInfo` 设为新 path 后续默认路由使用；仍向 Route 返回 false（继续其后 hook）。

DuckPhp 默认 ext 已装它。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class RouteHookRewrite extends ComponentBase`
- 常被前面 `prepend-outter` 于默认路由之前。

## 选项

`RouteHookRewrite::$options`:

| 选项 | 默认 | 说明 |
|---|---|---|
| `controller_url_prefix` | `''` | 可选 url 前缀（命中前去掉、重写后会补回）。 |
| `rewrite_map` | `[]` | 映射表 `匹配模板 => 内部url`；模板以 `~` 开头时被当正则。 |

运行时也可 `assignRewrite(键/数组)` 追加并 `getRewrites()` 查看。

## 使用方式

```php
use DuckPhp\Component\RouteHookRewrite;
RouteHookRewrite::_()->init([
  'rewrite_map' => [
     '/about'          => '/site/about',
     '~/user/([0-9]+)' => '/user/index?id=$1',
  ],
], App::_());
// GET /about -> 内部 /site/about
```

## 注意事项

- rewrite 只 happen exact或 regex；命中则返回 true 无 → 本组件返回 false 交给之后路由继续跑；若没命中也 false（就原本 path）。
- query 在你改写中会被保留合并（filter/新 merge）；命中时旧 `$_GET` 会存档到 `_SERVER[init_get]`、覆盖为命中 url 的查询集，便于后续 route 参数的 code 一致读取。
- 对 URL 匹配基础是 path（+ 可带查询）；基于 controller_url_prefix 关系处理前后缀。
- DirectoryMode 等调用 filteRewrite() 复用本转换（不给 hook 二次改变）。

## 方法列表

### 公共方法

    public static function Hook($path_info)
路由入口（由挂到 Route 的钩子触发）：静态壳，转发实例 `doHook`。

    public function assignRewrite($key, $value = null)
添加一条（数组形式则批量）rewrite 到内部映射 `rewrite_map`。

    public function getRewrites(): array
返回当前 rewrite 映射。

    public function replaceRegexUrl($input_url, $template_url, $new_url)
模板以 `~` 开头时的正则替换：改写 input 的 path 并合并 query；不匹配返回 `null`。

    public function replaceNormalUrl($input_url, $template_url, $new_url)
模板非 `~`：path 完全相等才命中；拼 `new_path` 并合并 query。

    public function filteRewrite($input_url)
逐条尝试（先 normal 后 regex），返回第一个命中结果或 `null`。

### 受保护方法

    protected function initOptions(array $options): void
把选项 `rewrite_map` 合并进内部映射。

    protected function initContext(object $context): void
向 `Route` 挂 `prepend-outter` 钩子（`[static::class,'Hook']`）。

    protected function changeRouteUrl(string $url): void
保存旧 GET 到 `init_get` 并设置新 query（上下文/全局）。

    protected function doHook(string $path_info): ?bool
前缀裁剪、用输入 query 拼 url → `filteRewrite`；命中则改 URL + `PathInfo`，返回 `false`（继续路由）。


## 相关链接

- [DuckPhp\Core\Route](Core-Route.md)（hook 挂载）
- [DuckPhp\Component\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md)（会调用 filteRewrite）
