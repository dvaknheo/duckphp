# 助手类
**重要，本文对应用开发者很重要**

## 相关类
- *[DuckPhp\Helper\HelperTrait](ref/Helper-HelperTrait.md)*  助手类公共 Trait。其他助手类都实现这里的方法。
- [DuckPhp\Helper\ViewHelper](ref/Helper-ViewHelper.md) 视图助手类
- [DuckPhp\Helper\ModelHelper](ref/Helper-ModelHelper.md) 模型助手类
- [DuckPhp\Helper\ControllerHelper](ref/Helper-ControllerHelper.md) 控制器助手类
- [DuckPhp\Helper\ServiceHelper](ref/Helper-ServiceHelper.md) 服务助手类
- *[DuckPhp\Helper\AppHelper](ref/Helper-AppHelper.md)* 应用助手类，一般不常用。

## 开始
助手类是应用开发者必须掌握的类。
我们搬出架构图。

![arch_full.gv.svg](arch_full.gv.svg)
文字版
```text
           /-> View-->ViewHelper
Controller --> Service ------------------------------ ---> Model
         \         \   \               \  /                  \
          \         \   \-> LibService ----> ExModel----------->ModelHelper
           \         \             \                
            \         ---------------->ServiceHelper
             \-->ControllerHelper
```
上述构架图省略了命名空间。



作为应用程序员， 你不能引入 DuckPHP 的任何东西，就当 DuckPHP 命名空间不存在。
核心程序员才去研究 DuckPHP 类的东西。

* 写 Model 你可能要引入 MY\Base\Helper\ModelHelper 助手类别名为 M 。
* 写 Serivce 你可能要引入 MY\Base\Helper\SerivceHelper 助手类别名为 S 。
* 写 Controller 你可能要引入 MY\Base\Helper\ControllerHelper 助手类别名为 C 。
* 写 View 你可能要引入 MY\Base\Helper\ViewHelper 助手类别名为 V 。
* 不能交叉引入其他层级的助手类。如果需要交叉，那么你就是错的。
* 小工程可以用直接使用入口类 MY\Base\App 类，这包含了上述类的公用方法。
* App 类包含助手类的全部内容。但是不推荐使用 App 类的助手类方法代替助手类。

工程的命名空间 MY 是 可调的。比如调整成 MyProject ,TheBigOneProject  等。
参见 $options['namespace'];

`AppHelper`  类并没出现在上图， AppHelper 类 的存在意义是什么呢？ 答案是用于 App 类的 助手函数参考， 当你要从 App 类里找出复杂的助手类，还不如在 AppHelper 里找。

问：为什么这个方法在助手类的声明里查不到?

答：查看相应助手类方法 GetExtendStaticMethodList() ，因为 \_\_callStatic($name, $arguments) 已经被接管。在里面实现。

## 助手类的公用静态方法

ThrowOn($flag,$messsage,$code=0,$exception_class=null)
    抛异常 如果 flag 成立，抛出 $exception_class(默认为 \Exception 类);
GetExtendStaticStaticMethodList()

    用来查看当前类有什么额外的静态方法。
\_\_callStatic

    静态方法已经被扩展, 会有额外代码，
IsDebug()

    判断是否在调试状态, 默认读取选项和设置字段里的 duckphp_is_debug
IsRealDebug()

    IsRealDebug 切莫乱用。用于环境设置为其他。比如线上环境，但是还是要特殊调试的场合。 如果没被接管，和 IsDebug() 一致。
Platform()

    获得当前所在平台,默认读取选项和设置字段里的 duckphp_platform，用于判断当前是哪台机器等
Logger($object = null)

    获得 psr 标准的 Logger 类。默认是 DuckPhp\Core\Logger 类。
trace_dump()

    调试状态下，查看当前堆栈，打印当前堆栈，类似 debug_print_backtrce(2)
var_dump(...$arg)

    调试状态下 Dump 当前变量，替代 var_dump
GetExtendStaticStaticMethodList()

    获得当前助手类扩展了什么，这个常用于查看核心代码给助手类加了什
ThrowOn($flag, $message, $code = 0, $exception_class = null)

    如果 flag 成立，那么抛出消息为 $message, code为 $code, $exception_class 的异常，如 $exception_class =null ，则默认为 Exception::class 的异常。
    另一调用方式：ThrowOn($flag, $message, $exception_class = null)  相当于 $code=0;
    参见 trait Duckphp\\Core\\ThrowOn
AssignExtendStaticMethod($key, $value = null)

    高级函数，参见 trait DuckPhp\\Core\\
CallExtendStaticMethod($name, $arguments)

    高级函数，参见 trait DuckPhp\\Core\\

## ViewHelper 视图助手类

本页面展示 ViewHelper 方法。 ViewHelper 是在View 里使用。 ViewHelper 默认的方法在 ControllerHelper 里都有。 但是 ViewHelper 不是作为 ControllerHelper 的子集。 
H($str)

	HTML 编码
L($str,$args=[])

	语言处理函数，后面的关联数组替换 '{$key}'
HL($str, $args=[])

	对语言处理后进行 HTML 编码
URL($url)

	获得相对 url 地址
ShowBlock($view, $data = null)

	包含下一个 $view ， 如果 $data =null 则带入所有当前作用域的变量。 否则带入 $data 关联数组的内容

## ServiceHelper 服务的助手类

 ServiceHelper 用于 Service 层。

    读取设置,设置默认在 config/setting.php 里， .env 的内容也会加进来
Config($key, $file_basename = 'config')
    读取配置，从 config/$file_basename.php 里读取配置
LoadConfig($file_basename)

    载入 config/$file_basename.php 的配置段
Setting($key);

    获得设置，默认设置文件是在  config/setting.php 。
    设置是敏感信息,不存在于版本控制里面。而配置是非敏感。
LoadConfig($key,$basename="config");

    载入配置，Config($key); 获得配置项目。默认配置文件是在  config/config.php 。
## ModelHelper

ModelHelper 用于 Model 层。 
ModelHelper 只有数据库的三个独特方法。
这几个方法在 ControllerHelper 里没有。
这几个方法不是 DuckPhp\Core\App 里的。
而是由 DuckPhp\App 加载 DuckPhp\Ext\DBManager 后添加的。
如何使用 DB 对象，看数据库部分的介绍。

DB($tag=null)

    获得 DB 数据库对象 ,第 $tag 个配置的数据库对象
DB_W()

    获得用于写入的 DB 对象,这是获得第 0 个配置列表里的数据库
DB_R()

    获得用于读取的 DB 对象，这是获得第 1 个配置列表里的数据库

## ControllerHelper 控制器的助手类

本页面展示 ContrlloerHelper 方法。 ContrlloerHelper 的方法很多很杂，但掌握了 ContrlloerHelper 方法，基本就掌握了用法 大致分为 【通用杂项】【路由处理】【异常管理】【跳转】【swoole 兼容】 【内容处理】 几块 内容处理和 ViewHelper 基本通用。 ControllerHelper 方法

### 显示相关

H
    【显示相关】见 ViewHelper 的 H 介绍
L

    【显示相关】见 ViewHelper 的 L 介绍
HL

    【显示相关】见 ViewHelper 的 HL 介绍
URL

    【显示相关】见 ViewHelper 的 URL 介绍
ShowBlock

    【显示相关】见 ViewHelper 的 ShowBlock 介绍
### 配置相关
Setting

    【配置相关】见 ServiceHelper 的 Setting 介绍
Config

    【配置相关】见 ServiceHelper 的 Config 介绍
LoadConfig

    【配置相关】见 ServiceHelper 的 LoadConfig 介绍
### 跳转相关
ExitRedirect($url, $exit = true)

    【跳转】跳转到站内URL ，$exit 为 true 则附加 exit()
ExitRedirectOutside($url, $exit = true)

    【跳转】跳转到站外URL, $exit 为 true 则附加 exit()
ExitRouteTo($url, $exit = true)

    【跳转】跳转到相对 url , $exit 为 true 则附 exit
Exit404($exit = true)

    【跳转】报 404，显示后续页面，$exit 为 true 则附加 exit()
ExitJson($ret, $exit = true)

    【跳转】输出 json 结果，$exit 为 true 则附加 exit()
getRouteCallingMethod()
### 路由相关
    【路由相关】获得当前的路由调用方法，用于权限判断等
setRouteCallingMethod

    【路由相关】设置当前的路由调用方法，用于跨方法调用时候 view 修正
getPathInfo()

    【路由相关】获得当前的 PATH_INFO
getParameters()

    【路由相关】获得路由重写相关的数据
### 内容处理
Show($data = [], $view = null)

    【内容处理】显示视图， 默认为 view/$view.php 的文件， 并会带上页眉页脚
setViewWrapper($head_file = null, $foot_file = null)

    【内容处理】设置页眉页脚
assignViewData($key, $value = null)

    【内容处理】分配视图变量，另一版本为 assignViewData($assoc);
Pager()
    【内容处理】获得分页器对象, 分页器参考 DuckPhp\Ext\Pager。 DuckPHP 只是做了最小的分页器
### 异常处理
assignExceptionHandler

    【异常处理】分配异常句柄
setMultiExceptionHandler
    【异常处理】设置多个异常处理
setDefaultExceptionHandler
    【异常处理】设置异常的默认处理
系统替代
header

    【系统替代】 header 函数以兼容命令行模式
setcookie()

    【系统替代】 setcookie 函数以兼容命令行模式
exit

    【系统替代】 退出函数，以便于接管
SG
    【swoole 兼容】 SG()-> 前缀替代 超全局变量做 swoole 兼容， 如 C::SG()->_GET[] , C::SG()->_POST[] 等。



## AppHelper

应用 助手的方法
```


    public static function SG()
    {
        return App::SG();
    }
    public static function GET($key, $default = null)
    {
        return static::SG()->_GET[$key] ?? $default;
    }
    public static function POST($key, $default = null)
    {
        return static::SG()->_POST[$key] ?? $default;
    }
    public static function REQUEST($key, $default = null)
    {
        return static::SG()->_REQUEST[$key] ?? $default;
    }
    public static function COOKIE($key, $default = null)
    {
        return static::SG()->_COOKIE[$key] ?? $default;
    }
    ////
    public static function Pager($object = null)
    {
        return App::Pager($object);
    }
    public static function PageNo()
    {
        return App::PageNo();
    }
    public static function PageSize($new_value = null)
    {
        return App::PageSize($new_value);
    }
    public static function PageHtml($total)
    {
        return  App::PageHtml($total);
    }
    
public static function OnException($ex)
{
    return App::OnException($ex);
}
public static function IsRunning()
{
    return App::IsRunning();
}
public static function InException()
{
    return App::InException();
}

public static function assignPathNamespace($path, $namespace = null)
{
    return App::assignPathNamespace($path, $namespace);
}
public static function addRouteHook($hook, $position, $once = true)
{
    return App::addRouteHook($hook, $position, $once);
}
public static function setUrlHandler($callback)
{
    return App::setUrlHandler($callback);
}
//
public static function set_exception_handler(callable $exception_handler)
{
    return App::set_exception_handler($exception_handler);
}
public static function register_shutdown_function(callable $callback, ...$args)
{
    return App::register_shutdown_function($callback, ...$args);
}
public static function session_start(array $options = [])
{
    return App::session_start($options);
}
public static function session_id($session_id = null)
{
    return App::session_id($session_id);
}
public static function session_destroy()
{
    return App::session_destroy();
}
public static function session_set_save_handler(\SessionHandlerInterface $handler)
{
    return App::session_set_save_handler($handler);
}
public static function &GLOBALS($k, $v = null)
{
    return App::GLOBALS($k, $v);
}
public static function &STATICS($k, $v = null, $_level = 1)
{
    return App::STATICS($k, $v, $_level + 1);
}
public static function &CLASS_STATICS($class_name, $var_name)
{
    return App::CLASS_STATICS($class_name, $var_name);
}
```

## 高级

扩展 助手类。 最直接的方式就是  添加静态方法。



一般工程，都会重写自己的助手类，而不是直接使用 DuckPhp 的助手类。
类似 自己对 ModelHelper 扩展:

```php
<?php
namespace MY\Base\Helper;

use DuckPhp\Helper\ModelHelper as Helper;

class ModelHelper extends Helper
{
    // override or add your code here
}

```
