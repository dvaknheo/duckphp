# DuckPhp\Component\RouteHookPathInfoCompat

无 PATH_INFO / 紧凑 URL 兼容路由钩子：当服务器不提供 canonical PATH_INFO 时，通过 query 键（如 `?_r=…`）携带路由路径；同时 URL 生成会编回这种 query 形式。

## 简介

`RouteHookPathInfoCompat extends ComponentBase` 在启用（init 时 `path_info_compact_enable` 非 false）时给 `Route` 装上：

- 一个**入向钩子**（`prepend-outter`·`Hook`）：在无 PATH_INFO 的环境，从 `$(module) 请求键`/`action_key` 还原出 path_info 写回 Route（保留一份 `PATH_INFO_OLD`）；
- 一个 **URL handler**（`Url`）：生成的 “相对 URL” 会被替换成 **basepath + `?{action_key}=路由 path**（若 `path_info_compact_class_key` 设置则模块/动作各挂一个键）。

于是类似 `?…index.php?_r=/foo/bar` 的环境，仍可与“干净路由人肉一样的映射”，生成的链接也同样 compact query 格式。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class RouteHookPathInfoCompat extends ComponentBase`
- 主题目标：`Route`（App 的默认 hook）。

## 选项

`RouteHookPathInfoCompat::$options`:

| 选项 | 默认 | 说明 |
|---|---|---|
| `path_info_compact_enable` | true | 开关（init 才装 hook/url handler）。 |
| `path_info_compact_action_key` | `'_r'` | 动作路由所在的 query 键。 |
| `path_info_compact_class_key` | `''` | 可选“模块（类路径段）”所在 query 键；留空则整路径都放 action键。 |

## 使用方式

启用（多数是 DuckPhp 内置把 PathInfoCompat 装进 ext；否则手动）：

```php
use DuckPhp\Component\RouteHookPathInfoCompat;
RouteHookPathInfoCompat::_()->init([
  'path_info_compact_enable' => true,     // 默认即此
], App::_());
```

然后请求 /old-host/index.php?_r=user/detail → 经 Hook 得到 path `/user/detail` 正常路由；`Url('user/detail')` 放出的链接为相同 query form。

## 注意事项

- 已有 PATH_INFO 时其实无需它；此组件“只在 compact/无 path_info urls 下补充”而 API 稳定。
- both Hook/Url 仍返回 false/或原始（对绝对同 URL 不做重写），交由 Route 其余 hook。

## 方法列表

    public function initContext(object $context): void（受保护 override）
enable=true 时：Route::addRouteHook([static::class,'Hook'],'prepend-outter') + Route::setUrlHandler([static::class,'Url'])。

    public static function Url($url = null)  → onUrl 生成 compact
把相对 url 转为  base+query _r=…（绝对 `/` 或没有时直接返回原）。

    public onUrl(?string $url = null): string
实现（支持 REQUEST_URI 基址、按 option keys、合并当前 query）。

    protected filteRewrite(string $url, &$flag=false): ?string
（预留给外部 rewrite 能力钩子，当前原样 return url。）

    public static function Hook($path_info) → _Hook
静态壳 → 实例 _Hook。

    public _Hook($path_info)
打包：从（context 或全局）request 的 class/action 两个键读 path 并 `Route::PathInfo(...)`;返回 false(继续路由)。

（注：模块参数 `$m` = value of `path_info_compact_class_key` 可为空串。）

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md) 挂载面
- 同族：RouteHookRewrite / RouteHookRouteMap / RouteHookResource
