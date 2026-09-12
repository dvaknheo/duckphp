# DuckPhp\Core\Route

路由解析与 URL 生成组件：把当前 URL(PATH_INFO) 映射到“控制器类 + 方法名”，并执行/生成路由。

## 简介

`Route` 是 DuckPHP 的默认路由核心。它承担两件事：

1. **入向路由**：把当前请求 URL（即 PATH_INFO）解析为要调用的控制器与动作，并通过反射找到可调用对象，最后执行它；
2. **出向路由**：提供统一的 `Url()/Res()/Domain()` 生成应用内 URL、静态资源 URL 与域名，让视图与代码不再手拼 URL。

它的行为几乎完全由 `$options` 控制：命名空间前缀、控制器后缀、方法前缀、欢迎类/欢迎方法、可选的 URL 前缀与资源前缀、控制器映射表等。路由也支持 4 + finally 层「路由钩子」(hook)，可在默认解析前后插入自定义逻辑。

Route 默认由 `DuckPhp\DuckPhp` 通过 `ext` 装配（`RouteHook*` 系列才是旁挂的额外钩子组件，`Route` 本身承载默认 MVC 匹配）。要脱离 App/Thread 独立使用可：`Route::RunQuickly([...])`。

实现上 `class Route extends ComponentBase` 还会 use 两个内联 trait —— `Route_Helper`（PATH_INFO/参数/当前路由调用信息等助手）与 `Route_UrlManager`（`_Url/_Res/_Domain`，URL 生成器）——它们的方法在下面都并入 `Route` 的方法列表中。

## 选项

`Route::$options` 全量（默认值取自源码）：

| 选项 | 默认值 | 说明 |
|---|---|---|
| `namespace` | `''` | 项目命名空间（不含 `\\`）。控制器最终全名 = `namespace + namespace_controller + 路径类名 + controller_class_postfix`。 |
| `namespace_controller` | `'Controller'` | 控制器所在子命名空间；以 `\\` 开头表示绝对子命名空间（不需拼上 `namespace`）。 |
| `controller_path_ext` | `''` | 路径扩展名过滤（如 `.html`）。设置后只有路径带此后缀才通过匹配，匹配成功后该后缀会被剔除。留空表示不校验。 |
| `controller_welcome_class` | `'Main'` | “欢迎控制器”：URL 很短 / 路径块为空时使用的控制器类名段。 |
| `controller_welcome_class_visible` | `false` | 是否允许路径真正显式写 `Main/…` 这一段。`false` 时显式出现欢迎控制器名会判 `E009` 并 404。 |
| `controller_welcome_method` | `'index'` | 当 URL 末尾“方法段”为空时作为默认方法名。 |
| `controller_class_adjust` | `''` | 类名/方法名**归一化规则**。字符串可含多个分号分隔指令：`uc_class`（路径最后一块 ucfirst）、`uc_method`（方法名 ucfirst）、`uc_full_class`（每段都 ucfirst）。也支持给它传数组。 |
| `controller_class_base` | `''` | 控制器基类约束。设置后目标控制器必须 `is_subclass_of` 该基类，否则 `E004`。字符串里可用 `~` 占位并在判断时替换为控制器命名空间前缀。 |
| `controller_class_postfix` | `'Controller'` | 类名后缀（读路径得到的块再补上它）。 |
| `controller_method_prefix` | `''` | 方法名前缀。留空意味着读到的路径方法段原样成为要调用的方法；很多宿主（如 `DuckPhp` 应用）把它配成 `action_`。 |
| `controller_prefix_post` | `'do_'` | POST 专用次级前缀。POST 请求时先尝试 `prefix + do_ + 方法名`（即 `action_do_*`），命中才替换调用。留空则不启用该逻辑。 |
| `controller_class_map` | `[]` | 控制器类名映射表：`旧全名 => 新全名`。可静态配置；也可运行时用 `replaceController()` 写入。 |
| `controller_resource_prefix` | `''` | 静态资源前缀。供 `_Res()/Res()` 生成资源 URL（可为 `https://cdn…` / `//cdn…` / 相对 `res/`）。 |
| `controller_url_prefix` | `''` | URL 路径前缀。会被参与 URL 校验与生成（见 `pathToClassAndMethod()` 与 `getUrlBasePath()`）。 |
| `controller_fix_mistake_path_info` | `true` | 当没有 PATH_INFO 且脚本即 `/index.php` 时，自动从 `REQUEST_URI` 的 path 补齐并回写 PATH_INFO。 |

各默认错误码（route_error 用, 详见注意事项 4）：`E001` url 前缀不匹配 / `E002, E003` 控制器类解析、反射失败 / `E004` 没继承 controller_class_base / `E005` 隐藏方法（`__…`）不可调 / `E006` 静态方法不可调 / `E007` 要调的方法反射不到 / `E008` 路径扩展名不匹配 / `E009` 显式写了不可见欢迎控制器。

## 使用方式

### 独立实例（脱离 App）

```php
use DuckPhp\Core\Route;
Route::RunQuickly([
    'namespace' => 'MyApp\\Sub',
    'controller_class_postfix'  => 'Controller',
    'controller_method_prefix'  => 'action_',
    'controller_url_prefix'     => 'api',
]);
```

### 在中部测试/高级绑定路径（可脱离真实请求）

```php
Route::_()->bind('/foo/bar', 'GET');   // 设定 path & 请求方法
$ok = Route::_()->run();              // 开始解析，返回是否成功
if (!$ok) { echo Route::_()->getRouteError(); }
```

### 读参数与当前调用信息

```php
$all = Route::Parameter();        // 全量路由参数（含绑定/路径块）
$id  = Route::Parameter('id', 9);
$c   = Route::_()->getRouteCallingClass();
$m   = Route::_()->getRouteCallingMethod();
```

### 出向 URL：方法 / 全局函数

```php
$link = Route::Url('user/info');        // 应用内相对 URL（含 url 前缀&path_info）
$res  = Route::Res('css/app.css');       // 静态资源（可按 controller_resource_prefix 出 CDN）
$dom  = Route::Domain();                // 不带协议 schema： //host
$dom2 = Route::Domain(true);            // 带 schema：      http(s)://host[:port]

$base = Route::_()->defaultUrlHandler(''); // 基路径
```

对应全局函数（CoreHelper 里映射的同名便捷方法）：`__url('...')`、`__res('...')`、`__domain()`、`__url_basepath()`。

### 路由钩子（重要扩展点）

4 个嵌套位置：`prepend-outter / prepend-inner / [默认路由] / append-inner / append-outter`，另加一个收尾组 `finally`（`finally-inner` / `finally-outter`）。每个钩子收到 `$path_info`；返回“真”则代表本轮已处理，钩子返回 true 会短路后续 process（finally hooks 还会相互短路直到一个 true break）。

```php
Route::_()->addRouteHook(function ($path_info) {
    if (substr($path_info, 0, 6) === '/rpc/') {
        myRpcRouter($path_info);      // 自己处理
        return true;                 // 吃掉，不再往下默认路由
    }
    return false;
}, 'prepend-inner');
```

`dumpAllRouteHooksAsString()` 可导出当前钩子做调试。

## 路由规则速查

- 缺省情形（前缀 none）把 URL 段解析：第一段及之后是“控制器路径段”，最后一段是方法段。
- `Welcome`（无额外段）：当路空 path 且无 pre hook 命中 → 走 欢迎类+欢迎方法。
- 完整类名格式：`{namespace_controller}\\{去后缀前类名}{controller_class_postfix}`；再做 `controller_class_map` 重映射。
- `controller_method_prefix` 空时方法名即 URL 末尾段；配成 `action_` 则 URL 片 `/bar` → 调 `action_bar`。
- POST 而 `controller_prefix_post` 命中时优先调 `<目标方法前缀>do_<方法段>`。（框架通常方法前缀=action_，得 `action_do_*`。）
- `getCallbackFromClassAndMethod()` 反射强约束：不能调以 `__` 开头的隐藏方法、不能调 static、目标需继承 `class_base`（如设了）。
- is_controller 判定单独可在 `isController($class)` 用。

## 配置示例

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'namespace' => 'Demo',
        'namespace_controller' => 'Controller',
        'controller_class_postfix' => 'Controller',
        'controller_method_prefix' => 'action_',
        'controller_welcome_class' => 'Main',
    ];
}
```

可控子应用/前缀或 CDN 型资源可参考 Options 所述在 app/顶级一并设置 controller_url_prefix/controller_resource_prefix。

## 注意事项

1. 类名与后缀：真正实例化的控制器类名 = 读路径 + `controller_class_postfix`；除了 welcome 类名可不需要对应 url 段，其余都需真实存在并可通过反射 `newInstance()` 实例化（无参构造）。
2. 反射规则的不可调用项：隐藏（以 `__` 开头）与静态方法会被拒（`E005/E006`）；方法不存在时 `E007`。避免把公开动作写成 static/受保护。
3. PHP 大小写：分析是按字符串路径字面前提。它在内部不做大小写折叠；推荐 Controller 采用 PSR-4（`FooController->action_bar`）保持一致大小写。
4. `controller_fix_mistake_path_info` 默认 true 会在 `/index.php` 当作虚拟 docroot 的项目内“修复”PATH_INFO；若你在 php -S 下意外反复出成 `/index.php` 前缀，可考虑此键或 URL 前缀的理解。
5. 错误可读：解析失败由默认钩子最后 `_On404()`/宿主统一处理，具体原因可通过 `Route::_()->getRouteError()` 读取上面带 `E0xx` 的说明文本（见选项一节下方列表）。
6. 无 options 数组外的全局状态：每个实例 Phase 隔离一个 Route；路径相关属性（route_error、calling_*、parameters）随实例保留，方便回调后继续读取“刚匹配到谁”。

## 全部选项

```php
        'namespace' => '',
        'namespace_controller' => 'Controller',

        'controller_path_ext' => '',
        'controller_welcome_class' => 'Main',
        'controller_welcome_class_visible' => false,
        'controller_welcome_method' => 'index',

        'controller_class_adjust' => '',
        'controller_class_base' => '',
        'controller_class_postfix' => 'Controller',
        'controller_method_prefix' => '',
        'controller_prefix_post' => 'do_',

        'controller_class_map' => [],

        'controller_resource_prefix' => '',
        'controller_url_prefix' => '',
        'controller_fix_mistake_path_info' => true,
```

## 方法列表

> 下列方法与 Route 及它 use 的两个内联 Trait（`Route_Helper`、`Route_UrlManager`）全部合并呈现；均为 `Route::_()` 可用成员。

### 公共方法

    public static function RunQuickly(array $options = [], ?callable $after_init = null)
启动：实例化 + init + 按环境 run（分离运行试验用）

    public static function Route()
返回当前（Phase 内）Route 实例

    public static function Parameter($key = null, $default = null)
返回路由参数：给 key 则单值+默认，否则整个数组

    public function _Parameter($key = null, $default = null)
Parameter 的实例实现

    public function bind($path_info, $request_method = 'GET')
在当前实例上绑 path & 请求方法（供无 HTTP 环境/单测使用）

    public function run()
执行一次路由：pre hooks →（默认回调）→ post hooks；返回是否成功

    public function clear()
finally 收尾钩子逐个执行；任一返回真即停

    public function forceFail()
把 is_failed 置真，让本次 run 视为失败（即使前面钩子成功）

    public function addRouteHook($callback, $position = 'append-outter', $once = true)
注册一个钩子到指定位置（prepend/append × inner/outer，或 finally-*）

    public function defaultToggleRouteCallback($enable = true)
开关“默认路由回调”是否尝试（用于只跑 hook 的纯定制路由）

    public function defaultRunRouteCallback($path_info = null)
用默认规则直接跑一次默认回调（内部 generate callback 并调用之）

    public function defaultGetRouteCallback($path_info)
把 path 解析成 [控制器对象,方法名]（映射/约束都在这条链路内做），失败返回 null 并写 route_error

    public function getControllerNamespacePrefix()
返回控制器调用的命名空间前缀（含尾 `\`）

    public function replaceController($old_class, $new_class)
运行时在 controller_class_map 里把旧控制器替换为新类

    public function getRunResult(): bool
（内用于 read is_failed）取“是否需要真失败”结果

    public function isController(string $class): bool
按 postfix/class_base 判定某全名是否会被当作控制器类

    public function dumpAllRouteHooksAsString()
导出 pre/run/post 各组钩子数组（var_export）便于抓 hook 调试

    public function setParameters($parameters)
整批覆盖路由参数

    public function getRouteError()
取最后一次 route_error 文本（`E0xx`），undefined 为

    public function getRouteCallingPath()
取本次匹配到的（去掉前缀的）路径段

    public function getRouteCallingClass()
取本次将要/已实例化的控制器全名

    public function getRouteCallingMethod()
取本次动作方法名

    public function setRouteCallingMethod($calling_method)
主动改写“当前动作方法”（rare）

    public static function PathInfo($path_info = null)
静态壳：转发实例 `_PathInfo`。

    public function _PathInfo($path_info = null)
传值则 setPathInfo（写 PATH_INFO），不传则返回当前 getPathInfo。

    public static function Url($url = null)
静态壳：转发实例 `_Url`。

    public function _Url($url = null)
生成应用内 URL：若设 `url_handler` 则委托之，否则走 `defaultUrlHandler`（绝对 `/` 保留、`?`/`#` 追加、相对拼基路径）。

    public static function Res($url = null)
静态壳：转发实例 `_Res`。

    public function _Res(?string $url = null)
结合 `controller_resource_prefix` 生成静态资源 URL：`//`/`https?://` 原样，相对先补基路径。

    public static function Domain($use_scheme = false)
静态壳：转发实例 `_Domain`。

    public function _Domain($use_scheme = false)
据 `REQUEST_SCHEME`/`HOST`/`SERVER_PORT` 拼 host：默认不含 scheme，`true` 时含。

    public function defaultUrlHandler($url = null)
默认 URL 生成：以 `/` 开头的原样返回，否则用基路径拼接；支持 `?` `#` 开头追加

    public function setUrlHandler($callback)
指定自定义 URL 处理器（覆盖默认 _Url 逻辑）

    public function getUrlHandler()
返回当前 url_handler（若有）

### 受保护方法

    protected function pathToClassAndMethod(string $path_info): ?array
主链路：去掉 url 前缀/扩展名，调 adjustClassBaseName，拼全名并用 class_map，返回[全名,方法]

    protected function adjustClassBaseName(string $path_info): array
把路径拆成路径块与最后一块“方法段”；按 welcome 规则归并；执行 controller_class_adjust

    protected function doControllerClassAdjust(array $blocks, string $method): array
执行 uc_method/uc_class/uc_full_class 系列归一

    protected function getCallbackFromClassAndMethod(string $full_class, string $method, string $path_info): ?array
反射校验与构造：能 class_base/隐藏/static 拒绝,反射 newInstance 并取 method，返回 [对象,方法]，失败写 route_error

    protected function adjustMethod(string $method, \ReflectionClass $ref): string
POST 且 controller_prefix_post 命中 `action_do_*` 时替换目标方法

    protected function getPathInfo(): string
取当前 PATH_INFO；支持 controller_fix_mistake_path_info 的 /index.php 修复并按需写回

    protected function setPathInfo(string $path_info): void
把 PATH_INFO 写到（上下文字段/全局）$_SERVER

    protected function getUrlBasePath(): string
从 DOCUMENT_ROOT/SCRIPT_FILENAME 推算脚本父目录 + controller_url_prefix 的 URL 基路径

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 组件基类（init/context）
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — serve 流程主持者（调用 Route::run）
- [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) — 无超全局上下文时提供隔离读取
- 更多路由相关组件：`RouteHookRewrite / RouteMap / Resource / PathInfoCompat` 等（见 reference 对应页）
- guide: [routing](../guide/routing.md)、[layers](../guide/layers.md)
