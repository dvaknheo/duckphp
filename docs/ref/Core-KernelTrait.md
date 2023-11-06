# DuckPhp\Core\KernelTrait
[toc]

## 简介
最核心的 Trait，仅完成基本流程。你通过 DuckPhp\DuckPhp 或 DuckPhp\Core\App 类来使用他。

## 引用

    [DuckPhp\\Core\\Console](Core-Console.md) 控制台
    [DuckPhp\\Core\\ExceptionManager](Core-ExceptionManager.md) 异常处理
    [Functions](Core-Functions.md) 全局函数
    [DuckPhp\\Core\\PhaseContainer](Core-PhaseContainer.md) 容器
    [DuckPhp\\Core\\Route](Core-Route.md) 路由
    [DuckPhp\\Core\\Runtime](Core-Runtime.md) 运行时状态
    [DuckPhp\\Core\\View](Core-View.md) 视图


## 选项

### 基本配置

            'path' => null,
基准目录，
如果没设置，将设置为 $_SERVER['SCRIPT_FILENAME']的父级目录。

            'namespace' => null,
基准命名空间，如果没设置，将设置为当前类的命名空间的上级命名空间，如MyProject\\System\\App => MyProject

            'path_namespace' => null,
命名空间路径,如果设置了本值，将会使用自动加载， 基准目录是 path。

            'override_class' => '',
**重要选项** 如果这个选项的类存在，则在init()的时候会切换到这个类完成后续初始化，并返回这个类的实例。

### 属性配置

            'is_debug' => false,
调试模式， 用于 IsDebug() 方法。

            'platform' => '',
平台， 自定义字符，用于 Platform() 方法。

            'ext' => [],
**重要选项** 扩展

        'setting_file' => 'setting',
设置文件名。

        'setting_file_enable' => true,
使用设置文件: $path/$path_config/$setting_file.php

        'use_env_file' => false,
使用 .env 文件
打开这项，可以读取 path 选项下的 env 文件

        'config_ext_file_map' => [],
额外的配置文件数组，用于 AppPluginTrait

        'setting_file_ignore_exists' => true,
如果设置文件不存在也不报错

### 开关配置

            'use_flag_by_setting' => true,
从设置文件里再入 is_debug,platform。
对应的设置选项是 duckphp_is_debug ，和 duckphp_platform

            'use_short_functions' => true,
使用短函数， \\_\\_url, \\_\\_h 等。
详见 Core\\Functions.php
            'skip_404_handler' => false,
不处理 404 ，用于配合其他框架使用。

            'skip_exception_check' => false,
不在 Run 流程检查异常，把异常抛出外面。用于配合其他框架使用


## 属性


## 方法
### 主流程

    public static function RunQuickly(array $options = [], callable $after_init = null): bool
快速开始，init() 后接 $after_init() 然后 run();

    public function init(array $options, object $context = null)
初始化
    public function run(): bool
运行，如果404，返回false。

    public function beforeRun()
不建议主动使用，加载运行状态数据，比如当前 URL 等。


### 事件方法

用于重写的方法默认都是空方法，预留用户功能。用于重写的方法都带有同名属性，可以用同名属性方式赋值

    protected function onPrepare()
准备阶段，你可以在这里替换默认类

    protected function onInit()
初始化完成

    protected function onBeforeRun()
运行阶段。不建议重写 run ，而是在这里添加运行阶段处理

    protected function onAfterRun()
运行完毕阶段执行的方法

    protected function onBeforeCreatePhases()

    protected function onAfterCreatePhases()

### 流程相关方法
    protected function checkOverride($override_class)
在 init() 里检测再入类。

    protected function initAfterOverride(array $options, object $context = null)
真正的 init 。依次执行 initOptions, onPrepare,initDefaultComponents，initExtentions,onInit

    protected function initOptions(array $options)
init() 中初始化选项

    protected function reloadFlags(): void
init() 中 DefaultComponents() 中从设置读取调试标志和平台标志

    protected function initExtentions(array $exts): void
初始化中，初始化扩展

    protected function getDefaultProjectNameSpace($class)
辅助方法，用于在 init() 中设置 namespace.

    protected function getDefaultProjectPath()
辅助方法，用于在 init() 中设置 path.

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

    public function getDynamicComponentClasses()
获得动态组件，在多次 run 的时候，这些组件都会 reset

## 流程详解

Kernel 这个 Trait 一般不直接使用。一般用的是 DuckPhp\Core\App ， 而直接的 DuckPhp\DuckPhp 类，则是把常见扩展加进去形成完善的框架。

Kernel 大致分为两个阶段

init() 初始化阶段，和 run 阶段

### init 初始化阶段

    处理是否是插件模式
    处理自动加载  AutoLoader::G()->init($options, $this)->run();
    处理异常管理 ExceptionManager::G()->init($exception_options, $this)->run();
    checkOverride() 检测如果有覆盖类，切入覆盖类（`$options['override_class']`）继续 
    接下来是 initAfterOverride;

### initAfterOverride 初始化阶段

    调整选项 initOptions()
    调整外界 initContext()
    调用用于重写的 onPrepare(); 
    
    
        
    加入扩展 initExtends()
    
    调用用于重写的  onInit();

### run 运行阶段
    run 阶段可重复调用
    处理 setBeforeRunHandler() 引入的 beforeRunHandlers
    异常准备
        beforeRun()；
            重制 RuntimeState 并设置为开始
            绑定路由
        * onBeforeRun ，可 override 处理这里了。
        ** 开始路由处理 Route::G()->run();
        如果返回 404 则 On404() 处理 404
    如果发生异常
        进入异常流程
    清理流程

-------------


    public static function Root()

    protected function checkSimpleMode($context)

    protected function getProjectPathFromClass($class, $use_parent_namespace = true)

    protected function initComponents(array $options, object $context = null)

    protected function runExtentions()

    public static function Current()

    public function getProjectPathFromClass($class, $use_parent_namespace = true)

    public function getContainer()

    public static function Phase($new = null)

    public function _Phase($new = null)

    protected function doInitComponents()

    protected function reloadFlags($context): void

    protected function loadSetting()

    protected function dealWithSettingFile()

    public function _Setting($key = null)


