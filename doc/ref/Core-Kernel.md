# Core\Kernel

## 简介
最核心的Trait，你通过 App 类来使用他。

## 引用

[Core\AutoLoader](ref/Core-AutoLoader.md)
[Core\Configer](ref/Core-Configer.md)
[Core\ExceptionManager](ref/Core-ExceptionManager.md)
[Core\Route](ref/Core-Route.md)
[Core\RuntimeState](ref/Core-RuntimeState.md)
[Core\View](ref/Core-View.md)
[Core\SuperGlobal](ref/Core-SuperGlobal.md)
[Core\Logger](ref/Core-Logger.md)

## 选项

### 基本配置
'path' => null,

    基准目录
'namespace' => 'MY',

    基准命名空间
'path_namespace' => 'app',

    命名空间路径
'override_class' => 'Base\App',

    重新进入的类。
'is_debug' => false,

    调试模式， 用于 IsDebug 方法。
'platform' => '',

    平台， 自定义字符，用于 Platform() 方法。
'ext' => [],
    
### 错误处理配置
'handle_all_dev_error' => true,
'handle_all_exception' => true,
'error_404' => null,          //'_sys/error-404',
'error_500' => null,          //'_sys/error-500',
'error_debug' => null,        //'_sys/error-debug',

### 开关配置
'reload_for_flags' => true,

    从设置文件中再次重载 is_debug 和 platform
'use_super_global' => false,

    使用 `SuperGlobal` 类处理超全局变量，默认关闭以节约微乎其微的性能。
'skip_view_notice_error' => true,

    Show() 函数关闭 notice 警告，以避免麻烦的处理。
'skip_404_handler' => false,

    不处理 404 ，用于配合其他框架使用。
'skip_plugin_mode_check' => false,

    跳过是否插件模式的检查， 打开以节约微乎其微的性能。
'skip_exception_check' => false,
    
    不在 Run 流程检查异常，把异常抛出外面，打开以节约微乎其微的性能。
'skip_fix_path_info' => false,

    修复默认没配置 PATH_INFO ，打开以节约性能
'skip_app_autoload' => false,
    
    **这里修改了 AutoLoader 的默认配置**
    不使用 AutoLoader 加载类，如果你不打算用AutoLoader类。打开以节约性能

## 方法
### 公开方法
public static function RunQuickly(array $options=[], callable $after_init=null): bool

    快速开始，init() 后接 $after_init() 然年后 run() 
public function init(array $options=[], object $context=null)

    初始化
public function run(): bool

    运行，如果404，返回false。
public function clear(): void

    不建议主动使用，用于清理现场。
public function replaceDefaultRunHandler(callable $handler = null): void

    不通过继承而是外挂替换默认的 Run 函数， `Ext\PluginForSwoole` 扩展用到。
public function addBeforeShowHandler($handler)

    挂接在显示前输入的方法。
### 重写用的方法
protected function onInit()

    初始阶段。因为类重入机制，不建议重写 init() 而是在这里
protected function onRun()

    运行阶段。不建议重写 run ，而是在这里添加运行阶段处理
protected function pluginModeInit(array $options, object $context = null)

    插件模式运行。这里用于插件方法
### 流程相关方法。
protected function checkOverride($options)

    在 init() 里检测重入类。
protected function initOptions($options = [])

    init() 中初始化
protected function reloadFlags(): void

    init() 中从设置读取调试标志和平台标志
protected function initExtentions(array $exts): void

    初始化扩展
protected function fixPathInfo(&$serverData)

    修复Path_INFO
## 详解

Kernel 这个 Trait 不直接处理，一般直接用的是 Core\App ， 而直接的 App 类，则是把常见扩展加进去形成完善的框架。

### 流程说明
Kernel 大致分为两个阶段
init() 初始化阶段，和 run 阶段

run 阶段，通过 PluginForSwoole 插件,调用 replaceDefaultRunHandler 修改默认流程

run 阶段可重复调用