# DuckPhp\Ext\MiniRoute

## 简介

`MiniRoute` 是极简版 MVC 路由（`Core\Route` 的子集）：只做「PATH_INFO → 控制器类/方法 → 反射校验 → 调用」这一件事，**没有 hook 链、重写、资源路由等扩展**。适合只需要基础路由的轻量场景。

失败时不会抛异常，而是把错误码写入 `$route_error`（如 `E001` 前缀不符、`E002/E003` 类不存在、`E005` 隐藏方法、`E006` 静态方法、`E007` 方法不存在、`E008` 后缀不符、`E009` 欢迎类不可见），可用 `getRouteError()` 读取。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class MiniRoute extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `namespace` | `''` | 应用命名空间。 |
| `namespace_controller` | `'Controller'` | 控制器子命名空间。 |
| `controller_path_ext` | `''` | 路径后缀要求（如 `.php`，为空不校验）。 |
| `controller_welcome_class` | `'Main'` | 欢迎控制器（空路径时用）。 |
| `controller_welcome_class_visible` | `false` | 欢迎类是否允许通过 URL 显式访问。 |
| `controller_welcome_method` | `'index'` | 欢迎方法。 |
| `controller_class_postfix` | `''` | 控制器类后缀。 |
| `controller_method_prefix` | `''` | 方法名前缀（如 `action_`）。 |
| `controller_class_map` | `[]` | 类名映射（替换）。 |
| `controller_resource_prefix` | `''` | 资源前缀（保留）。 |
| `controller_url_prefix` | `''` | URL 前缀（剥除）。 |

## 使用方式

```php
\DuckPhp\Ext\MiniRoute::_()->init([
    'namespace' => 'MyProject',
    'namespace_controller' => 'Controller',
    'controller_welcome_class' => 'Main',
    'controller_method_prefix' => 'action_',
], $app);

$ok = MiniRoute::Route()->run();          // 解析当前 PATH_INFO 并调用
$err = MiniRoute::Route()->getRouteError();
$u = MiniRoute::Url('Main/hello');        // URL 生成
```

## 注意事项

- `run()`：PATH_INFO → `defaultGetRouteCallback()` 得到 `[对象, 方法]` 后调用；解析失败返回 `false`。
- 路径解析：URL 前缀剥除（`controller_url_prefix`）→ 可选后缀校验（`controller_path_ext`）→ 末段为方法、其余为控制器路径（`/`→`\`）→ 空控制器用 welcome class → 拼命名空间/前后缀/映射。
- 反射校验：类必须可实例化（`ReflectionClass::newInstance`）、方法非静态、方法名不以 `_` 开头（隐藏方法保护）。
- `PathInfo()/_PathInfo()` 与 `Url/Res/Domain` 提供 PATH_INFO 读写与 URL 生成（读取/写入 `$_SERVER['PATH_INFO']`，超全局经 `SuperGlobal` 语义）。

## 方法列表

### 公共方法

    public static function Route()
返回当前路由实例。

    public function run(): bool
解析当前 PATH_INFO 并调用控制器方法；失败返回 `false`。

    public function defaultGetRouteCallback(string $path_info): ?array
解析出 `[对象, 方法]`（含反射校验）；失败返回 `null` 并记录 `route_error`。

    public function getControllerNamespacePrefix(): string
拼出控制器命名空间前缀（含尾部 `\`）。

    public function replaceController($old_class, $new_class)
登记控制器类替换（写入 `controller_class_map`）。

    public static function PathInfo($path_info = null)
读/设 PATH_INFO（静态壳）。

    public function _PathInfo($path_info = null)
读/设 PATH_INFO（传值则 set，否则 get）。

    public function getRouteError(): ?string
返回最近一次路由错误（如 `E003: …`）。

    public function getRouteCallingPath(): string
当前命中的调用路径。

    public function getRouteCallingClass(): string
当前命中的控制器类。

    public function getRouteCallingMethod(): string
当前命中的方法名。

    public function setRouteCallingMethod($method)
设置当前调用方法名。

    public static function Url($url = null)
生成应用内 URL（静态壳）。

    public static function Res($url = null)
生成资源 URL（静态壳）。

    public static function Domain($use_scheme = false)
取当前域名（静态壳）。

    public function _Url($url = null)
生成应用内 URL。

    public function _Res($url = null)
生成资源 URL。

    public function _Domain($use_scheme = false)
取当前域名。

### 受保护方法

    protected function pathToClassAndMethod(string $path_info): ?array
把 PATH_INFO 映射为 `[完整类名, 方法名]`（含前缀/映射逻辑）。

    protected function getPathInfo(): string
读取 `$_SERVER['PATH_INFO']`。

    protected function setPathInfo(string $path_info): void
写入 `$_SERVER['PATH_INFO']`。

    protected function getUrlBasePath(): string
计算 URL 基路径（供 Url/Res 使用）。

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md) — 完整版路由（本类是子集）
