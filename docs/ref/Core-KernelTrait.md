# DuckPhp\Core\KernelTrait
[toc]

## 简介
最核心的 Trait，仅完成基本流程。你通过 DuckPhp\DuckPhp 或 DuckPhp\Core\App 类来使用他。

## 引用

- 控制台 [DuckPhp\\Core\\Console](Core-Console.md)
- 异常处理器 [DuckPhp\\Core\\ExceptionManager](Core-ExceptionManager.md)
- 全局函数 [Functions](Core-Functions.md)
- 相位容器 [DuckPhp\\Core\\PhaseContainer](Core-PhaseContainer.md)
- 路由 [DuckPhp\\Core\\Route](Core-Route.md)
- 运行时 [DuckPhp\\Core\\Runtime](Core-Runtime.md)


## 选项

### 基本配置

        'path' => null,
基准目录，如果没设置，将设置为`$_SERVER['SCRIPT_FILENAME']`的父级目录。

        'override_class' => null,
如果这个选项的类存在，则且新建 `override_class` 初始化

        'override_class_from' => null,
`override_class`切过去的时候会在此保存旧的`override_class`

        'cli_enable' => true,
启用命令行模式

        'is_debug' => false,
调试模式， 用于 `IsDebug()` 方法。

        'ext' => [],
扩展，保存 类名=>选项对

        'skip_404' => false,
不处理 404 ，用于配合其他框架使用。

        'on_init' => null,
初始化完成后处理回调

        'namespace' => null,
基准命名空间，如果没设置，将设置为当前类的命名空间的上级命名空间，如MyProject\\System\\App => MyProject

        'skip_exception_check' => false,
不在 Run 流程检查异常，把异常抛出外面。用于配合其他框架使用

        'setting_file' => 'config/DuckPhpSettings.config.php',
设置文件名。仅根应用有效

        'setting_file_enable' => true,
使用设置文件: $path/$path_config/$setting_file.php 仅根应用有效

        'use_env_file' => false,
使用 .env 文件。 仅根应用有效
打开这项，可以读取 path 选项下的 env 文件

        'setting_file_ignore_exists' => true,
如果设置文件不存在也不报错 仅根应用有效

        'exception_reporter' => null,
异常报告类

        'exception_reporter_for_class' => null,
异常报告仅针对的异常

### 来自控制器的选项

        'namespace_controller' => 'Controller',

        'controller_path_ext' => '',

        'controller_welcome_class' => 'Main',

        'controller_welcome_class_visible' => false,

        'controller_welcome_method' => 'index',

        'controller_class_base' => '',

        'controller_class_postfix' => 'Controller',

        'controller_method_prefix' => 'action_',

        'controller_prefix_post' => 'do_', //TODO remove it

        'controller_class_map' => [],

        'controller_resource_prefix' => '',

        'controller_url_prefix' => '',
### 来自运行时的选项
        'use_output_buffer' => false,

        'path_runtime' => 'runtime',

### 来自控制台的选项
        'cli_command_alias' => [],

        'cli_default_command_class' => '',

        'cli_command_method_prefix' => 'command_',

        'cli_command_default' => 'help',

### 来自异常管理器的选项


## 方法
### 静态方法

    public static function RunQuickly(array $options = [], callable $after_init = null): bool
快速开始，init() 后接 $after_init() 然后 run();

    public static function Current()
当前App

    public static function Root()
当前根App

    public static function Setting($key = null, $default = null)
    public function _Setting($key = null, $default = null)
获取设置

    public static function Phase($new = null)
    public function _Phase($new = null)
当前相位，返回之前相位

### 主流程
    public function init(array $options, object $context = null)
初始化

    public function run(): bool
运行，如果404，返回false。



### 默认行为
    public static function On404(): void
    public function _On404(): void
处理 404

    public static function OnDefaultException($ex): void
    public function _OnDefaultException($ex): void
处理异常

    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
处理开发模式错误

### 公开辅助方法

    public function getProjectPathFromClass($class, $use_parent_namespace = true)
从类中获得默认工程路径

    public function getContainer()
获得 PhaseContainer 容器


    public function isRoot()
是否是根App

### 流程相关方法

    protected function initOptions(array $options)
init() 中初始化选项

    protected function reloadFlags($context): void
init() 中 DefaultComponents() 中从设置读取调试标志和平台标志

    protected function initExtentions(array $exts): void
初始化中，初始化扩展

    protected function getDefaultProjectNameSpace($class)
辅助方法，用于在 init() 中设置 namespace.

    protected function getDefaultProjectPath()
辅助方法，用于在 init() 中设置 path.

    protected function initContainer($context)
初始化容器

    protected function initComponents(array $options, object $context = null)
初始化默认组件

    protected function doInitComponents()
初始化默认组件，方便继承用

    protected function loadSetting()    
    protected function dealWithSettingFile()
    protected function dealWithEnvFile()
处理设置

    protected function initException($options)
初始化异常处理

    protected function runExtentions()
运行 扩展
    protected function runException($ex)
处理异常

    protected function loadSetting()
在初始化中加载设置


### 事件方法

用于重写的方法默认都是空方法，预留用户功能。用于重写的方法都带有同名属性，可以用同名属性方式赋值

    protected function onBeforeCreatePhases()
    protected function onAfterCreatePhases()
创建相位的重载

    protected function onPrepare()
准备阶段，你可以在这里替换默认类

    protected function onBeforeExtentionInit()

    protected function onInit()
初始化完成

    protected function onBeforeRun()
运行阶段。不建议重写 run ，而是在这里添加运行阶段处理

    protected function onAfterRun()
运行完毕阶段执行的方法



## 流程详解

Kernel 这个 Trait 一般不直接使用。一般用的是 DuckPhp\Core\App ， 而更直接的 DuckPhp\DuckPhp 类，则是把常见扩展加进去形成完善的框架。

Kernel 大致分为两个阶段

init() 初始化阶段，和 run 阶段

### init 初始化阶段流程
#### 开始阶段
init()，一开始填充 path 和 namespace 选项
载入 全局函数
初始化选项
如果有 override_class 选项，切到 override_class 执行

检查相位，

#### 检查相位做的工作

检查完相位 调用 onPrepare

#### reloadflags

#### 装载异常管理器

#### initComponets

调用 onBeforeExtentionInit 触发相关事件

#### 加载扩展

onInit



### run 阶段



