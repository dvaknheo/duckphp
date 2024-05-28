# DuckPhp\Core\App
[toc]

## 简介
Core 目录下的微框架入口
## 依赖关系
* 组件基类 [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
* 核心Trait [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)
* 日志类 [DuckPhp\Core\Logger](Core-Logger.md)
* 路由类 [DuckPhp\Core\Route](Core-Route.md)
* 超全局变量 [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md)
* 系统同名函数替代 [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md)
* 视图类 [DuckPhp\Core\View](Core-View.md)



## 选项

### 专有选项

        'path_runtime' => 'runtime',
运行时目录，需要可写

        'alias' => null,
别名，目前只用于视图目录

        'default_exception_do_log' => true,
发生异常时候记录日志


        'close_resource_at_output' => false,
输出时候关闭资源输出（仅供第三方扩展参考

        'html_handler' => null,
HTML编码函数

        'lang_handler' => null,
语言编码回调

        'error_404' => null,          //'_sys/error-404',
404 错误处理的View或者回调，仅根应用有效

        'error_500' => null,          //'_sys/error-500',
500 错误处理的View或者回调，仅根应用有效


### 来自日志组件

        'path_log' => 'runtime',

        'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',

        'log_prefix' => 'DuckPhpLog',

### 来自视图组件

        'path_view' => 'view',

        'view_skip_notice_error' => true,
### 来自超全局变量组件

        'superglobal_auto_define' => false,

### 扩充自 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) 的默认选项。


详情见 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) 参考文档

## 方法


### 独有的静态方法

    public static function Platform()
    public function _Platform()

    public static function IsDebug()
    public function _IsDebug()

    public static function IsRealDebug()
    public function _IsRealDebug()


    public static function _($object = null)

### 动态方法

    public function isInstalled()
是否已经安装

    public function install($options, $parent_options = [])
    public function version()
版本，目前在 命令行中用到

    public function skip404Handler()
跳过 404 处理

    public function getProjectPath()
获得工程路径

    public function getRuntimePath()
获得可写的运行路径

    public function getOverrideableFile($path_sub, $file, $use_override = true)
获得子应用的覆盖文件

    public function adjustViewFile($view)
调整默认 View 的回调

    public function onBeforeOutput()
输出前的回调

提供Show 的回调

### 接管流程的函数
    public function __construct()
构造函数

    protected function doInitComponents()
额外初始化

    public function _On404(): void
处理 404

    public function _OnDefaultException($ex): void
处理异常

    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
处理开发期错误

    
## 说明

