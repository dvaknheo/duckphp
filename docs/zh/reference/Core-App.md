# DuckPhp\Core\App

DuckPHP 的应用基类：`use KernelTrait` 并叠加一批“系统级”能力，是 `DuckPhp` / `DuckPhpAllInOne` 的父类。

## 简介

`App` 位于 `DuckPhp\Core\App`：`class App extends ComponentBase { use KernelTrait }`，并对外部可见成员做了一层整理。

- 它把 KernelTrait 里几个“装组件 / 跑一次请求”的入口改名后 override，从而在工作骨干里插入框架的静态组件（`SystemWrapper`、`Logger`、`CoreHelper`、`Route`、`View`、`SuperGlobal`）与设置(supplier→setting)加载。
- 构造时把来自 kernel / core / 用户 options 按 `array_replace_recursive` 合并进 `public $options`，并清空临时属性；`core_options` 设定了若干个进程级/错误/设置文件默认配置。
- 还提供把 Framework 公共能力做网关的一组静态/实例便利：`Setting()/version()/Platform()`、命名 debug 判断 `IsDebug/IsHiddenDebug`、错误页/维护页回调、可覆盖文件查询、URL/lang 简单口味等。

一般项目并不直接 new `App`；通常你定义一个 `class XApp extends DuckPhp\DuckPhp` 或 `DuckPhpAllInOne`，其父链最终会用本类的那些组件/设置。只有在你**自行组装底层**时才直接 extend `App`。

<br/>(类源里 `require_once __DIR__.'/Functions.php'` 已含于本入口。)

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class App extends ComponentBase`
- 使用 Trait：`KernelTrait`
- 用 `as` 改名再 override：`initComponents`→`Kernel_initComponents`、`prepareServe`→…、`initComponentsOfRoot`→…、`Inner`、`Dynmic`；然后在自身方法里调用 `Kernel_xxx()` 保持父骨架行为。
- 常量：`VERSION=1.4.1` 、 `EXT_SKIP_INIT=-1`、`EXT_DISABLE=0`、`EXT_DEFAULT=1`、`EXT_FOLLOW_APP=2`、`EXT_RENEW=3`。
- 公共属性：`$options`、`$setting`（由 `options['setting']` 起，叠加可选 `.env` 与设置文件合并而成）、加上 Kernel 内核态字段。

## 选项

`App::core_options`（默认）你几乎总能改；下表为实际生效的键：

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path_runtime` | `'runtime'` | 运行期相对项目根目录（或绝对路径）。`getRuntimePath()` 返回。 |
| `path_config` | `'config'` | 配置文件目录（`getConfigFile()` 基于它+Phase覆盖查找）。 |
| `default_exception_do_log` | `true` | 默认异常处理器是否写日志。 |
| `close_resource_at_output` | `false` | 输出结束是否统一关闭/回收资源（默认关闭）。 |
| `html_handler` | `null` | （预留/扩展用）任意 html 处理器回调。 |
| `lang_handler` | `null` | 传入后 `lang()`/`langText()` 将优先走它，而不再 fallback 简易替换。 |
| `is_maintain` | `false` | 维护标记。命中时 `prepareServe()` 渲维护页（`error_maintain`）。 |
| `skip_404` | `false` | 跳过 404 展示（`skip404Handler()` 会置真）。 |
| `error_404` | `null` | 404 时用（路径或可调用）。null → 内置 404 占位/开发信息。 |
| `error_500` | `null` | 异常默认总页（路径或可调用）。null → debug 下详细、非 debug 精简。 |
| `error_debug` | `null` | 开发期错误视图/可调用。null → 内置 fieldset 回执。 |
| `error_maintain` | `null` | 维护页视图/可调用。null → 内置 “Maintaining.”。 |
| `setting_file` | `'config/DuckPhpSettings.config.php'` | Setting 文件（相对根或绝对）。 |
| `setting_file_ignore_exists` | `true` | 设置文件缺失时是否忽略（并不抛错）。 |
| `setting_file_enable` | `true` | 是否加载设置文件。 |
| `use_env_file` | `false` | 为真则在 loadSetting 一并读根 `.env`(INI) 合并。 |
| `setting` | `[]` | 直接以数组形式给出的应用设置（setting）初值；作为 `loadSetting()` 的起点，随后再合并 `.env`（可选）与设置文件。 |
| `installed` | `false` | 应用是否“已安装”（false 会触发安装跳转）。 |
| `url_install` | `'install'` | 未安装时跳到的安装 URL。 |
| `exception_map` | `[]` | 异常类映射表（`原异常类 => 替代异常类`）；`ProjectThrowOn/BusinessThrowOn/ControllerThrowOn` 抛异常前会先按它替换类名。 |

## 使用方式

在真正的项目，写自己 App 最常用 `DuckPhp`；如果连 DuckPhp 那层也不要，可这样继承：

```php
    namespace Demo\System;
    use DuckPhp\Core\App;

    class App extends App {
    public $options = [
    'namespace' => 'Demo',
    'path'      => __DIR__.'/../..',
    'error_404' => '_sys/error_404',
    ];

    protected function onPrepare(): void { parent::onPrepare(); /* 预整理 */ }
    }
```

然后可用 `Demo\System\App::RunQuickly([])` 启动（Kernel 提供）——它会走 Settings 已读 + 组件装载 + root 路由环节。

### 静态便捷在业务/模板里的入口

```php
    \App::version();      // (Demo\System\App)1.4.1
    \App::Setting('site_name','');  //读取 setting
    \App::_()->getRuntimePath();    // .../runtime/
    \App::IsDebug();                // 是否 debug
    \App::Platform();               // duckphp_platform(可直接看到是什么环境)
```

### 加载顺序摘要

- `init()`内：kernel 完成 options/phase/exception → `onPrepare()`（root 时会读 `.env`+Setting 到 `$this->setting`）→ `initComponents…` 由 App 插入 System components → `…`后续 Kernel 流程一致。
- `serve()` 前：`prepareServe()` 维护态会先输出维护页。

## 配置示例

```php
    class App extends \DuckPhp\DuckPhp {
    public $options = [
    'path_runtime'   => 'runtime',
    'setting_file_enable' => true,
    'setting_file'    => 'config/site.config.php',
    'use_env_file'    => true,
    'error_404'       => '_sys/error_404',
    'error_500'       => '_sys/error_500',
    'error_maintain'  => '_sys/maintain',
    'is_maintain'     => false,
    'installed'       => true,
    ];
    }
```

## 注意事项

1. 不要在运行时直接改 `$setting`→依赖 `Setting()`；需改由文件/覆改内核使一致。
2. 维护(`is_maintain` 或 Setting 里的 `duckphp_is_maintain=true`)会让你看到维护页而不是正常业务。
3. `haltInitInBaseClass()` 抛 `DuckPhpSystemException`；**这是刻意的**——用来阻止直接对 Base `App::_()->init()`（[DuckPhp](DuckPhp.md) 里把它覆盖成空实现，所以 DuckPhp 可以初始化）。
4. `getOverrideableFile()` 有两种用途，用 `$must_exist` 区分：读文件传 `true`（不存在给你 `null`，别再拿返回值当存在性判断），要一个「尚不存在的落盘路径」传 `false`（返回最后一个候选路径）。
5. `installed=false` 时请求会 302 到 `url_install`（看 `checkInstallToPage`），上线请设成 true。
6. `_On404/_OnDefaultException/_OnDevErrorHandler` 均会调 `onBeforeOutput()`（如有 View page），可提前把资源 header 等放 `onBeforeOutput()`。
7. `_DEPRECATED` 提示仅在 debug+inited 才输出；undefined 会回触 dev handler。

## 全部选项

```php
    protected $core_options = [
    'path_runtime' => 'runtime',
    'path_config' => 'config',

    'default_exception_do_log' => true,
    'close_resource_at_output' => false,
    'html_handler' => null,
    'lang_handler' => null,

    'is_maintain' => false,
    'error_404' => null,
    'error_500' => null,
    'error_debug' => null,
    'error_maintain' => null,

    'setting_file' => 'config/DuckPhpSettings.config.php',
    'setting_file_ignore_exists' => true,
    'setting_file_enable' => true,
    'use_env_file' => false,
    'setting' => [],

    'installed' => false,
    'url_install' => 'install',
    'exception_map' => [],
    ];
```

## 方法列表

本文件只收录 `App.php` 内 `class App …` 自身定义的方法；由 KernelTrait 引入的同名 shell 见 [Core-KernelTrait](Core-KernelTrait.md)，不在此重复。

### 公共方法

    public function __construct()
合并 kernel/core/common 与用户 options 进 $options；清空临时 options 容器并记录 this_class

    public function version()
返回 `(类名)VERSION` 版本标识（调试/CLI 用）

    public static function Setting($key = null, $default = null)
静态读设置：委托根应用 _Setting

    public function _Setting($key = null, $default = null)
读根 setting：有 key→`Root()->setting[$key] ?? default`；无→返回整个 setting

    public static function Platform()
读设置 `duckphp_platform` 的平台静态壳

    public function _Platform()
实例：返回 duckphp_platform 值

    public static function IsDebug()
静态判断 debug：委托 __IsDebug()

    public function _IsDebug()
debug = setting(duckphp_is_debug) ∨ 根 options is_debug ∨ 本 options is_debug

    public static function IsHiddenDebug()
真实 debug 静态壳

    public function _IsHiddenDebug()
默认等同 `_IsDebug()`（上层若要区分再做覆盖）

    public function _On404(): void
404 兜底：header 404，error_404 可回调/视图执行；缺省时输出占位并在 debug 下附 route error；is_root/skip 先 return

    public function _OnDefaultException($ex): void
默认异常出口：回 phase、可选日志、500 header，走 error_500 或缺省详/占位；避免 ininit 前置 error_500

    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
调试错误处理：非 debug return；装配 errno/…/shortfile，用 error_debug 或内建 

    public function isAbsPath($path)
判断路径是否为绝对路径：`/` 开头、盘符（如 `C:\`、`C:/`）、或 `\\` 开头；参数先转字符串。

    public function slashDir($path)
把路径尾部统一成目录分隔符结尾（`''` 原样返回，否则 `rtrim('/\\')` 后补 `DIRECTORY_SEPARATOR`）。

    public function getOverrideableFile($path_sub, $file, $use_override = true, $must_exist = false)
Phase 感知文件查找：从当前 phase 起逐层回退找 `path_sub/子目录/file`，命中即返回；`$must_exist=true` 时**只认真实存在**的文件（都没有则返回 `null`），`false` 时返回最后一个候选路径（可尚不存在，供写入用）

    public function getConfigFile(string $file, bool $must_exist = false)
取 config 下某文件（基底 `path_config` + `getOverrideableFile`）；`$must_exist=true` 且文件不存在时返回 `null`

    public function getRuntimePath(): string
返回运行期绝对目录（path_runtime 相对则拼根 path）

    public function skip404Handler()
置 options[skip_404]=true，跳过 404 展示

    public function onBeforeOutput()
输出前钩子；框架决定 View 渲染时先调它（空基）

    public function _Show(array $data, string $view = '')
渲染一带数据视图；视图名缺省=当前 route path；先 onBeforeOutput

    public function checkInstallToPage(?string $url_install = null): void
未安装：302 到安装 URL（缺省用 options[url_install]）并 exit

    public function lang($str, $args = [], $fallback = null)
极简翻译：lang_handler 回调优先；否则按 {key} 替换 / fallback

    public function langText(string $desc, array $args = []): string
把文本里的 `[[key|fallback]]` 片段经 lang() 翻译（可传递 args）

### 受保护方法

    protected function onPrepare(): void
根应用时在 Kernel 的 onPrepare 阶段调用 loadSetting() 加载设置

    protected function initComponentsOfRoot($components, $default): void
根组件补 SystemWrapper/Logger/CoreHelper(EXT_SKIP_INIT) 后走 Kernel_initComponentsOfRoot

    protected function initComponentsOfInner($classes, $default): void
内层并入 View(FOLLOW_APP) 后走 Kernel…Inner

    protected function initComponentsOfDynmic($classes, $default): void
动态层并入 SuperGlobal 与 View(FOLLOW_APP) 后走 Kernel…Dynmic

    protected function prepareServe()
调 Kernel…prepareServe 后：命中维护设置(error_maintain / view)则直接出维护页

    protected function loadSetting(): void
以 options[setting] 起，按 use_env_file、setting_file_enable 相继 dealWithEnvFile/SettingFile

    protected function dealWithEnvFile(): void
parse_ini_file(根/.env) 并入 $this->setting

    protected function dealWithSettingFile(): void
按绝对/相对解析 config 文件，require 返回数组并入 setting；不存在且 !ignore 抛 ErrorException

    protected function haltInitInBaseClass(): void
static::class===self::class(直接 App)时抛 “DO NOT INIT App!”

## 相关链接

- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — 本类的骨架（init/run/services）
- [DuckPhp\DuckPhp](DuckPhp.md) / [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) — 常用入口
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\View](Core-View.md)、[DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)、[DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md)、[DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md)
- guide：[configuration](../guide/configuration.md)、[lifecycle](../guide/lifecycle.md)
