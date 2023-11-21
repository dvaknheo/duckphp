# DuckPhp\Core\App
[toc]

## 简介
Core 目录下的微框架入口
## 依赖关系
* 组件基类 [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
* 系统同名函数替代Trait [DuckPhp\Core\SystemWrapperTrait](Core-SystemWrapperTrait.md)
* 核心Trait [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)
* 日志类 [DuckPhp\Core\Logger](Core-Logger.md)
* 异常管理类 [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
* 路由类 [DuckPhp\Core\Route](Core-Route.md)
* 运行时数据类 [DuckPhp\Core\RuntimeState](Core-RuntimeState.md)
* 视图类 [DuckPhp\Core\View](Core-View.md)



## 选项

### 专有选项
        'html_handler' => null,

        'lang_handler' => null,

        'default_exception_do_log' => true,
发生异常时候记录日志

        'default_exception_self_display' => true,（废弃）
发生异常的时候如有可能，调用异常类的 display() 方法。

        'close_resource_at_output' => false,
输出时候关闭资源输出（仅供第三方扩展参考

        'error_404' => null,          //'_sys/error-404',
404 错误处理 的View或者回调

        'error_500' => null,          //'_sys/error-500',
500 错误处理 View或者回调

        'error_debug' => null,        //'_sys/error-debug',
调试的View或者回调

        'path_runtime' => 'runtime',

        'alias' => null,

        'path_log' => 'runtime',

        'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',

        'log_prefix' => 'DuckPhpLog',

        'path_view' => 'view',

        'view_skip_notice_error' => true,

        'superglobal_auto_define' => false,
### 扩充 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) 的默认选项。


详情见 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) 参考文档

```php
    protected static $options_default = [
            //// not override options ////
            'use_autoloader' => false,
            'skip_plugin_mode_check' => false,
            
            //// basic config ////
            'path' => null,
            'namespace' => null,
            'override_class' => '',
            //
            //// properties ////
            'is_debug' => false,
            'platform' => '',
            'ext' => [],
            
            'use_flag_by_setting' => true,
            'use_short_functions' => true,
            
            'skip_404_handler' => false,
            'skip_exception_check' => false,
        ];
```

## 方法


### 独有的静态方法

## 详解
DuckPhp\Core\App 类 可以视为几个类的组合

### 作为内核的 App 入口类
详见 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)

### 助手函数，助手类，和本类的关系

助手类的静态方法都调用本类的静态方法实现。

相关代码请参考相应助手类方法。 

 + [AdvanceHelper](Helper-AdvanceHelper.md)
 + [BusinessHelper](Helper-BusinessHelper.md)
 + [ControllerHelper](Helper-ControllerHelper.md)
 + [ModelHelper](Helper-ModelHelper.md)
 + [ViewHelper](Helper-ViewHelper.md)



### 动态方法


    public function version()
版本，目前在 命令行中用到

    public function addBeforeShowHandler($handler)
高级

    public function removeBeforeShowHandler($handler)
高级

    public function skip404Handler()
跳过 404 处理，用于协程类



### 接管流程的函数
    public function __construct()
构造函数

    public function _On404(): void
处理 404

    public function _OnDefaultException($ex): void
处理异常

    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
处理开发期错误




### 内部实现函数

这些都是内部没下划线前缀的静态方法的动态实现。 不用 protected 是因为想让非继承的类也能修改实现。

### 内部函数


    protected function onBeforeOutput()

    protected function doInitComponents()

    public function getProjectPath()

    public function getRuntimePath()

    public function getOverrideableFile($path_sub, $file)

    public function onBeforeOutput()

    public function adjustViewFile($view)

## 说明



    public static function AdminId()

    public static function UserId()

    public function _Platform()

    public function _IsDebug()

    public function _IsRealDebug()

    public function _Event()

    public function _Pager($object = null)



    public static function Setting($key = null, $default = null)


    public function isInstalled()

    public function install($options, $parent_options = [])

    public static function Admin($new = null)

    public static function AdminData()

    public static function User($new = null)

    public static function UserData()

    public function _Admin($new = null)

    public function _AdminData()

    public function _User($new = null)

    public function _UserData()

    public function _AdminId()

    public function _UserId()