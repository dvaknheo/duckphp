# Core\Kernel
[toc]

## 简介
最核心的Trait，你通过 App 类来使用他。

## 引用
[Core\\AutoLoader](ref/Core-AutoLoader.md)   自动加载
[Core\\Configer](ref/Core-Configer.md) 配置
[Core\\ExceptionManager](ref/Core-ExceptionManager.md) 异常处理
[Core\\Route](ref/Core-Route.md) 路由
[Core\\RuntimeState](ref/Core-RuntimeState.md) 运行时状态
[Core\\View](ref/Core-View.md) 视图
[Core\\SuperGlobal](ref/Core-SuperGlobal.md) 超全局变量
[Core\\Logger](ref/Core-Logger.md) 日志管理。

## 选项
use 开始的选项都是默认 true ，skip 开头的都是 false;

### 子类无法更改的选项
'use_autoloader' => true,

 	使用自动加载
'skip_plugin_mode_check' => false,

 	跳过插件模式
'handle_all_dev_error' => true,

除了开发的错误 

'handle_all_exception' => true,

	处理所有异常

'override_class' => 'Base\App', 

    重新进入的切换的子类

'override_class'=>'Base\App',**重要选项**

    基于 namespace ,如果这个选项的类存在，则在init()的时候会切换到这个类完成后续初始化，并返回这个类的实例。
    注意到 app/Base/App.php 这个文件的类 MY\Base\App extends DuckPhp\DuckPhp;
    如果以  \ 开头则是绝对 命名空间

### 基本配置

'path' => null,

    基准目录
'namespace' => 'MY',

    基准命名空间
'path_namespace' => 'app',

    命名空间路径

'is_debug' => false,

    调试模式， 用于 IsDebug() 方法。
'platform' => '',

    平台， 自定义字符，用于 Platform() 方法。
'ext' => [],
    
    扩展
'log_errors' => true,

	记录错误 // 内核流程中未使用，但被 App 类使用。


### 开关配置
'use_flag_by_setting' => true,

    从设置文件中再次重载 is_debug 和 platform
'use_super_global' => false,

    使用 `SuperGlobal` 类处理超全局变量，默认关闭以节约微乎其微的性能。
'use_short_functions' => false,

    允许使用短函数

'skip_404_handler' => false,

    不处理 404 ，用于配合其他框架使用。

'skip_exception_check' => false,
    
    不在 Run 流程检查异常，把异常抛出外面，打开以节约微乎其微的性能。
'skip_fix_path_info' => false,

    修复默认没配置 PATH_INFO ，打开以节约性能
### 错误处理配置

'error_404' => null,          //'_sys/error-404',
'error_500' => null,          //'_sys/error-500',
'error_debug' => null,        //'_sys/error-debug',

## 属性
    protected $options_project = []; 用于子类自己的 options

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

重写的方法默认都是空方法，用于在重写的时候实现留给功能

protected function onPrepare()

    准备阶段，你可以在这里替换默认类 (v1.2.5)
protected function onInit()

    初始化完成
protected function onRun()

    运行阶段。不建议重写 run ，而是在这里添加运行阶段处理
### 流程相关方法
protected function checkOverride($options)

    在 init() 里检测重入类。
protected function initAfterOverride($options)

    真正的 init 按顺序执行 initOptions, onPrepare,initDefaultComponents，initExtentions,onInit
protected function initOptions($options = [])

    init() 中初始化选项
protected function reloadFlags(): void

    init() 中 DefaultComponents() 中从设置读取调试标志和平台标志
protected function initExtentions(array $exts): void

    初始化中，初始化扩展
protected function fixPathInfo(&$serverData)

    运行中 修复PATH_INFO
## 详解

Kernel 这个 Trait 一般不直接使用。一般直接用的是 Core\App ， 而直接的 App 类，则是把常见扩展加进去形成完善的框架。

### 流程说明
Kernel 大致分为两个阶段

init() 初始化阶段，和 run 阶段


run 阶段，通过 PluginForSwoole 插件,调用 replaceDefaultRunHandler 修改默认流程

run 阶段可重复调用

### run() 详解

