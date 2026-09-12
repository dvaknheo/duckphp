# DuckPhp\Component\RouteHookResource

让 Route 还能挡掉并直接打出静态资源（res）的钩子组件；顺带提供把 `res/` 拷贝到发布（document root）的 `cloneResource` 工具。

## 简介

`RouteHookResource extends ComponentBase` 两面：

1. 作为路由钩子（`initContext` 在检测到 resource 前缀时给 `Route` 挂 `append-outter` 的 `Hook`）：如果某 `path_info` 落在 resource 前缀且能对应到 `res/{file}` 的真实文件（且非 `.php`、无 `..`），则直接发送该「 mime+内容」并 `return true`（命中）。这就是它把静态资源从框架内 res 目录“经 DuckPhp 也能发布”的方式；
2. `cloneResource($force,&$info)`：控制台辅助——把 `res/` 内容部署/拷贝到 document root 所在的资源前缀目标目录（供静态真实 server 无需框架也可直接给出），内含一套递归 copy/建目录/防重写的受保护小工具。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class RouteHookResource extends ComponentBase`
- 交付面：资源 URL 可写成相对（如以 `controller_resource_prefix` 指定 CDN/子目录，或留空用默认 res 相对）。

## 选项

`RouteHookResource::$options`：

| 选项 | 默认 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径（相对路径基准）。 |
| `path_resource` | `'res'` | 资源源目录（默认 `res`）。 |
| `path_document` | `'public'` | 发布根目录名（clone 目标）。 |
| `controller_url_prefix` | null | 路由资源 URL 前缀段（可选）。 |
| `controller_resource_prefix` | `''` | 访问前缀（例如 `res/` 或 `//cdn/…`）；决定 hook/clone 行为。 |

## 使用方式

挂路由时 `RouteHookResource` 常与 `controller_resource_prefix`/CDN 搭配选择。如果需要让静态被打出而不依赖额外 web server，则希望把 URL/前缀指向本地、开启该 hook，之后访问 `…/{prefix}x.png`：

```php
RouteHookResource::_()->init([
    'path' => '',
    'path_resource' => 'res',
    'controller_resource_prefix' => 'res/',
], App::_());
// 之后 GET /res/logo.png -> 从 <path>/res/logo.png 读并给 mimeHeader 输出
```

部署一条龙（把 res 内容 po 到 doc root 相应位置、避免手工 diff）：

```php
RouteHookResource::_()->init([], App::_());
RouteHookResource::_()->cloneResource();   // force=false，已有文件则不覆盖
RouteHookResource::_()->cloneResource(true, $info); // force 覆盖；$info 收集拷贝日志
```

## 注意事项

- 只服务“存在的真实文件”；对 `.php`/越权`..`返回 false（交给后续 Route/404）。
- res 内容克隆避免手动同步；document_root 目标通常就是 web 能直接读的地方。
- 多 target（CDN/远程）场景资源前缀为 `//`/`https://` 时不再本地 hook，而由 CDN 直接——由前缀判断决定（见 code）。

## 方法列表

### 公共方法

    public static function Hook($path_info)
静态钩子入口：转发实例 `_Hook`。

    public function _Hook($path_info)
路由钩子实现：decode、前缀判定、防越权/php，存在则 content-type输出文件并 return true；否则 false。

    public function cloneResource($force = false, &$info = '')
把 <path>/<path_resource> 内容拷贝（建 docroot对应前缀）到 document_root；force 决定是否跳过已存在文件。

    protected function initContext(object $context): void（受保护）
当启用该资源前缀时向 Route 挂 `append-outter` hook。

### 受保护助手（cloneResource 用）

    protected function get_dest_dir(string $path_parent, string $path): string
按路径层级建立并返回目标目录（存在则跳）。

    protected function copy_dir($source, $dest, $force=false, &$info='')
递归把 source 内文件拷到 dest；force=false 遇已存在则取消输出 `File Exsits`。

    protected function check_files_exist(string $source, string $dest, array $files, string &$info): bool
扫某 dest 已有则 true。

    protected function create_directories(string $dest, array $files, string &$info): bool
根据文件相对路径预建目录，mk 失败返回 false。

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md) hook append-outter
- route resources: controller_resource_prefix（见 Core-Route options）
- clone 经 app 命令行使用（Component/DuckPhpInstaller 等）
