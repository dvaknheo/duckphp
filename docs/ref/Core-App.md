# DuckPhp\Core\App
[toc]

## 简介
Core 目录下的微框架入口
## 依赖关系
* 组件基类 [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
* 可扩展静态Trait [DuckPhp\Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md)
* 系统同名函数替代Trait [DuckPhp\Core\SystemWrapperTrait](Core-SystemWrapperTrait.md)
* 核心Trait [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)
* 日志类 [DuckPhp\Core\Logger](Core-Logger.md)
* 自动加载类 [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
* 配置类 [DuckPhp\Core\Configer](Core-Configer.md)
* 异常管理类 [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
* 路由类 [DuckPhp\Core\Route](Core-Route.md)
* 运行时数据类 [DuckPhp\Core\RuntimeState](Core-RuntimeState.md)
* 视图类 [DuckPhp\Core\View](Core-View.md)



## 选项

### 专有选项
'default_exception_do_log' => true,

    发生异常时候记录日志
'default_exception_self_display' => true,

    发生异常的时候如有可能，调用异常类的 display() 方法。
'close_resource_at_output' => false,
    
    输出时候关闭资源输出（仅供第三方扩展参考
"injected_helper_map" =>'', 

    injected_helper_map 比较复杂待文档。和助手类映射相关。 v1.2.8-dev

'error_404' => null,          //'_sys/error-404',

    404 错误处理 的View或者回调
'error_500' => null,          //'_sys/error-500',

    500 错误处理 View或者回调
'error_debug' => null,        //'_sys/error-debug',

    调试的View或者回调


### 扩充 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) 的默认选项。


详情见 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) 参考文档

    use_autoloader
    path
    namespace
    skip_plugin_mode_check
    override_class
    is_debug
    platform
    use_flag_by_setting
    use_super_global
    use_short_functions
    skip_404_handler
    skip_exception_check
    ext

## 方法


### 独有的静态方法

RunQuickly() 快速运行
Blank() 空函数

system_wrapper_replace 替换系统默认函数
system_wrapper_get_providers 能提供的系统默认函数列表

On404() 默认404 处理
OnDefaultException 默认异常处理
OnDevErrorHandler 默认开发期错误处理
Route() 路由类
public function version()    得到当前版本

​    \DuckPhp\DuckPhp::runAutoLoader();

## 详解
DuckPhp\Core\App 类 可以视为几个类的组合

### 作为内核的 App 入口类
详见 DuckPhp\Core\KernelTrait

### 助手类引用的静态方法

助手类的静态方法都调用本类的静态方法实现。

为了避免重复，请在相关助手类里查看参考

相关代码请参考 

 + [HelperTrait](Helper-AppHelper.md)
 + [AppHelper](Helper-AppHelper.md)
 + [BusinessHelper](Helper-BusinessHelper.md)
 + [ControllerHelper](Helper-ControllerHelper.md)
 + [ModelHelper](Helper-ModelHelper.md)
 + [ViewHelper](Helper-ViewHelper.md)

或者，按分类
trait Core_SystemWrapper
trait Core_Helper
Core_NotImplemented
Core_Glue
Core_SuperGlobal

### 关于 injected_helper_map

## 全方法索引

//待脚本