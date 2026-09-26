# DuckPhp\Core\KernelTrait

应用核心的“骨架逻辑”：被 `DuckPhp\Core\App`（及其派生 `DuckPhp`） `use`，承载初始化、运行、Phase 与生命周期编排。

## 简介

`KernelTrait` 把“一个应用是什么、怎么跑”这件事写在一个 Trait 里，由应用类（如 `App`）通过 `use KernelTrait` 引入。它负责：

- 声明/合并应用的默认 options（`$kernel_options`）以及若干运行属性；
- 提供 `RunQuickly()`（一键启动）、`Root()`（根应用）、`Phase()`（Phase 读写）等静态入口；
- 在 `init()` 里完成一次应用初始化：推导 path/namespace → 注册进 Phase 容器 → 初始化异常系统 → 走 `onPrepare()` 钩子 → 初始化 `Console`/`Route` 及 `ext` 扩展 → 递归初始化 `app` 子应用 → `onInited()`；
- 提供 Web(`serve()`)、CLI(`execute()`) 与统一 `run()`；
- 提供一串“内部状态可读”的实例方法（根?、Phase 名、调用中信息等)与可覆盖的钩子方法（`onPrepare/onInit/onInited/onRequest`…）。

你不一定直接用它：只要继承 `DuckPhp\Core\App` 或 `DuckPhp\DuckPhp`，子类可以通过重写 `$options` / 那些受保护钩子来定制，而不用触碰本 Trait 源码。

## 选项

`KernelTrait` 以 `protected $kernel_options` 定义默认值（构造时与各层合并进 `public $options`）。下表为其**实际生效**的键（源码里其后被注释的部分如 `controller_*` 是历史/留给上层同options 自行启用而默认不在这里生效）。

| 选项 | 默认值 | 说明 |
|---|---|---|
| `is_debug` | `false` | 调试开关字段（源码注释：“no use, just align”，仅占位对齐；真正取舍请用 App::IsDebug() 等汇总逻辑）。 |
| `path` | `null` | 项目根目录。显式缺省时按 `SCRIPT_FILENAME`…上下两层推导后在 `initOptions()` 中补成绝对路径并以 `/` 结尾。 |
| `namespace` | `null` | 项目默认命名空间。缺省由当前实现类反推（`getDefaultProjectNameSpace`）。 |
| `name` | `''` | 该应用的短名；用于子应用 Phase 命名。 |
| `app` | `[]` | 子应用声明表。形如 `[类 => ['namespace'=>…,'controller_url_prefix'=>…, …]]`；`initChildren()` 会把它们逐个做成独立子 Phase。 |
| `cmd` | `[]` | 当前应用要注册的 CLI 命令类映射（`类 => "默认方法前缀"`）。由 Console 消费。 |
| `data` | `[]` | 预留数据槽（源码注释“no use in, just align”）。 |
| `ext` | `[]` | 层内要初始化的扩展组件列表：`类 => true|数组|'@方法'|EXT_* 常量`。在初始化阶段按 `initOptions→…→initComponents…OfExt` 生效。 |
| `cli_enable` | `true` | CLI 环境下是否进入命令处理（`execute()`）。为 `false` 即使 CLI 也走 Web。 |
| `skip_exception_check` | `false` | 为 `true` 时 `runException` 直接把异常再次抛出，不再进入框架统一的异常分发。 |
| `use_exit_exception` | `true` | 是否定义全局 `__EXIT_EXCEPTION`（为 `ExitException::class`）以让 exit 语义可捕获。 |
| `override_from` | `null` | 记“自己被谁改写覆盖”的类名。`override_class` 流程使用。 |
| `override_class` | `null` | 若给出，`init()` 时用此类接管当前初始化（构造后以 `override_from=get_class(原)` 重入 init）。 |
| `app_children_allow_mix_mode` | `true` | 允许子应用简写：用表键当 `controller_url_prefix`、值内含 `'class'` 指定真实子应用类。false 则要求结构化写法。 |
| `on_init` | `null` | (`callable`) 初始化中 `onInit()` 阶段被调。 |
| `on_inited` | `null` | (`callable`) 全部初始化完毕 `onInited()` 阶段被调。 |
| `on_request` | `null` | (`callable`) 每次 `serve()` 开头被调。 |

> 说明：`is_debug` / `data` 两条代码里注明只为对齐占位；不要依赖它判断状态。

## 使用方式

一个最小的、继承这个 Trait 语义的应用仍以继承 `App`/`DuckPhp` 为准；这里给出直接基于该 Trait 结构的行为示例：

```php
// 一般你不直接 use 它；等效写法是继承 DuckPhp\DuckPhp / App：

class MyApp extends DuckPhp\DuckPhp {
    public $options = [
        'namespace' => 'MyProj',
        'app'   => [ /* 可有多个子应用 */ ],
        'cmd'   => [ MyCmd::class => 'command_', ],
        'ext'   => [ MyFeature::class => true, ],
        'cli_enable' => true,
    ];
}

MyApp::RunQuickly([]);   // cli? execute/内部？否：先 init 后按环境走 execute()/serve()
```

要点：

- 想让某类“成为一个 App”：需让它最终 `use KernelTrait`（通常继承 `App` 即具备）。
- 一次请求/命令的生命入口按需 `$app->run()` 或静态 `RunQuickly()`。
- `init()` 会以 `$context==null → is_root=true` 区分顶层；任何 `context` 传子的 init 会成为子应用(phase 后缀)。
- 需要执行 CLI 命令：注册见 `regConsoleCommand()`/`cmd`，管理在 `Console`。

## 生命周期顺序

`init()` 内顺序（供决定何时在哪里留钩子）：

```
override_class?> → haltInitInBaseClass → initOptions(options, 推导 path/ns)
→ is_root / is_cli 判定 → initContainer(context)      // 进入某 Phase、登记 instance
→ initException(options)                               // 异常回调绑定（根部）
→ onPrepare() ├ loader 差异 / 顶部 require 等可放
→ initComponents()                                     // Root: Console；每 Phase：Console+Route；ext
    → initComponentsOfRoot / Init    …… subclass 可注入组件
→ onInit()        （执行 on_init 回调）
→ initChildren($options['app'])       // 逐个递归 init -> 子 Phase
→ is_inited = true
→ onInited()      （执行 on_inited 回调）
```

Web 一次请求 `serve()`：

```
prepareServe()   // phaseToCurrent + initComponentsOfDynmic(RENEW)
onRequest()      // on_request 回调
try{ Runtime::run(); Route::run() || runChildren() }
        // 全 miss → 自身 phaseToCurrent→ _On404()
catch(Throwable) → runException()（记录 last phase→…→ExceptionManager::CallException）
finally → phaseToCurrent; Route::clear(); Runtime::clear()
```

CLI `execute()`：

```
try{ return Console::run(); } catch(Throwable){ runException(); return true; }
```

`run()`按 `cli_enable` 选择 execute 或 serve。

## 注意事项

1. 根与子判定：`static::class === self::class` 或 is-a `self::class` 且无 context —— `is_root` 结果影响是否能 execute 与阶段组件装载；应在 init 后通过 `isRoot()` 查询而不是凭想象。
2. Phase 唯一性：子阶段名字重复会在 `initContainer` 抛 `DuckPhpSystemException` 提示 “set … 'name'options”。给同名子应用子不同的 `name`。
3. 所有 `on_*` 系列回调优先于钩子方法又被钩子调用：不要既传 `on_init` 回调又 override `onInit()` 造成双跑。
4. `run()` 语义：Root CLI 且 `cli_enable` 才会走 execute；否则 Web。强制“CLI 也进 serve”用 `cli_enable=false`。
5. 覆盖注意：要让某个钩子生效记得 `parent::`（若想要父骨架逻辑）按需调用。
6. 需访问静态入口：`RunQuickly/Root/Phase/FromCurrentParent/SwitchRootPhase` 已在 `public static` 提供。
7. `Root($switch_phase = false)` 取的是根 Phase 中登记的实例，**不会**因为取值而改变当前 Phase；只有显式传真值才会顺带切回根 Phase（子应用里「拿根实例并切回根」一步到位的用法）。切阶段那一步内部写的是 `App::Phase(self::$ROOT_PHASE)`（硬编码在 `App` 上），而返回的实例是 `self::class` 在根 Phase 的登记项——以源码为准。

## 全部选项

```php
    protected $kernel_options = [
        'is_debug' => false, // no use, just align
        'path' => null,
        'namespace' => null,
        'name' => '',

        'app' => [],
        'cmd' => [],
        'data' => [], // no use in, just align
        'ext' => [],

        'cli_enable' => true,
        'skip_exception_check' => false,
        'use_exit_exception' => true,
        'override_from' => null,
        'override_class' => null,
        'app_children_allow_mix_mode' => true,

        'on_init' => null,
        'on_inited' => null,
        'on_request' => null,
    ];
```

## 方法列表

> 本 Trait 定义的方法在被 `App` 类引入后会成为其可用成员；下列仍以源码顺序、按“公共 / 受保护”分组列出全部。

### 公共方法

    public static function RunQuickly(array $options = [], ?callable $after_init = null): bool
一键：`static::_()->init($options)`（可选 after init 回调）后，root 且 cli_enable → execute()，否则 serve()

    public static function Root($switch_phase = false)
返回当前类在根 Phase 中的实例（没有时为 null）；`$switch_phase` 为真时顺带把当前 Phase 切回根 Phase

    public static function Phase($new = null)
Phase 静态口：不传返回当前 Phase 名；传串则切换（返回旧值）

    public static function FromCurrentParent()
若处于某个“父的 app 子应用”Phase，切回父实例并返回；否则返回 null

    public static function SwitchRootPhase($phase)
重设根 Phase 与共享容器名（`$phase.'#shared'`）；并同步 PhaseContainer 的 current/default

    public function _Phase(?string $new = null): string
Phase 实例实现；有参时切走并记录 last_phase，返回切换前

    public function isRoot()
是否根 App（无父 context）

    public function isCli()
CLI 调用中 —— root 下返回 is_cli；子应用委托 root

    public function getLastPhase()
返回上一次 Phase 名

    public function getThisClassName()
返回本实例登记到容器时使用的类名（this_class）

    public function getThisParent()
从当前“子 Phase”推导其父并切回去，返回父实例

    public function toThisChild($class)
切到 `class` 子应用对应的 Phase 并返回该实例（不存在返回 null）

    public function getThisPhaseName()
返回当前实例的 Phase 名字符串

    public function getThisCommandPrefix()
由此 Phase 名派生 CLI 命令前缀（分隔转为 `-`）

    public function regConsoleCommand(string $class, string $default_method = 'command_')
为当前 Phase 注册命令类到 Console，并写入 $options['cmd']

    public function getProjectPath()
返回根应用 `options['path']`

    public function init(array $options, ?object $context = null)
应用初始化总入口（若 override_class 会转交新类重入）。顺序见“生命周期顺序”一节

    public function toChildPhase(string $class)
该 $class 已被登记为子应用则切到其 Phase 并 true；否则 false

    public function run(): bool
按 cli_enable 分发到 execute() / serve()

    public function serve(): bool
执行一次请求级 route ……（pre→Route/children→404→异常→finally hooks+clear）

    public function execute(): bool
CLI：`Console::run()`；异常经 runException 包装

    public function phaseToCurrent(): void
把 PhaseContainer 的 current 拨回本实例 Phase

    public static function On404(): void
void 外层：调实例 _On404

    public static function OnDefaultException($ex): void
丢给实例 _OnDefaultException（供 ExceptionManager 注册 root 默认器）

    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
静态壳转发 _OnDevErrorHandler

    public function _On404(): void
兜底 404 回执（此 trait 的 print “no found”；App 层以自定义视图覆盖）

    public function _OnDefaultException($ex): void
兜底默认异常（trait 仅打印占位；App 层做日志/页面）

    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
兜底开发期错误（trait 仅打印占位；App 层扩展）

### 受保护方法

    protected function initOptions(array $options): void
合并补齐：namespace/path 缺省推导并把 path 变成绝对路径

    protected function getDefaultProjectNameSpace(?string $class): string
由实现类命名空间剥末两层得到项目命名空间（X\System\App → X）

    protected function getDefaultProjectPath(): string
按 SCRIPT_FILENAME 上级推导当前项目目录（看不到外全局时取自 __SUPERGLOBAL_CONTEXT 的 _SERVER）

    protected function initContainer(?object $context = null): bool
根则建默认容器并设 current；子则生成 `<父Phase>:<name>` 唯一 Phase 并注册，登记 self/static/override 实例

    protected function createLocalObject(string $class, ?object $object = null): object
在当前 Phase 新造局部对象（不跨 Phase 共享）并登记

    protected function initException(array $options): void
配置异常系统：default/dev 处理器绑定 root self，非根关闭 handle_all；可定义 __EXIT_EXCEPTION

    protected function initComponents()
初始化本应用核心：Root 先 Console(FOLLOW)，每层 Route(FOLLOW)，再消化 options['ext']

    protected function initComponentsOfRoot($classes, $default): void
将 classes 标记为 public 后初始化（根阶段组件）

    protected function initComponentsOfInner($classes, $default): void
初始化（含子应用也共有的 Route/Console），并注册该 Phase 的命令前缀与 cmd

    protected function initComponentsOfExt($classes, $default): void
按 ext 表初始化扩展

    protected function initComponentsOfDynmic($classes, $default): void
“动态组件”装配口（prepareServe 每请求 RENEW 用之）

    protected function initComponentsByClasseOptions(array $exts, $default): void
遍历 `类=>opts` 表逐个交给 initExtensionsByOptions（空项自动滤除）

    protected function initExtensionsByOptions(string $class, $options, $default)
判断 opts（EXT_* 常量/数组/`@方法`/options 键名）并对应 init/新建/跳过某个扩展

    protected function initChildren(array $apps): void
规范化+逐个初始化子应用 `app`，记录每子 `__phase__` 并 phaseToCurrent 回父

    protected function prepareServe()
serve 前置：回到本 Phase 并对“动态组件”做 RENEW 重建

    protected function runException(\Throwable $ex): void
异常统处：记 last phase、Runtime::onException 后转 ExceptionManager；skip_exception 时直接 throw

    protected function runChildren(): bool
按 options['app'] 顺序让每个子应用尝试 serve()，任一成功即返回

    protected function onAfterCreatePhases(): void
(空) 根容器 Phase 建完后的钩子，供子类扩展

    protected function haltInitInBaseClass(): void
(空) “不许直接初始化基类”拦截口；App 会用它抛异常，DuckPhp 放开

    protected function onPrepare(): void
(空) 初始化准备钩子（options 合并后、组件 init 前）

    protected function onInit(): void
执行 $options['on_init'] 回调（若有）

    protected function onInited(): void
执行 $options['on_inited'] 回调（若有）

    protected function onRequest(): void
`serve()` 每次请求初执行 $options['on_request'] 回调（若有）

## 相关链接

- [DuckPhp\Core\App](Core-App.md) — `use KernelTrait` 的应用基类
- [DuckPhp\DuckPhp](DuckPhp.md) / [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) — 常用入口子类
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 组件基础 init / context
- [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) — Phase 容器（本 Trait 依赖）
- [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Core\Console](Core-Console.md) — serve 与 execute 的目标
- guide：[lifecycle](../guide/lifecycle.md)、[advanced-phase](../guide/advanced-phase.md)、[cli](../guide/cli.md)
