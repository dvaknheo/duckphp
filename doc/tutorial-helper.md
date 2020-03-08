# 助手类
**重要，本文对应用开发者很重要**
## 相关类
- [DuckPhp\Helper\ControllerHelper](ref/Helper-ControllerHelper.md) 控制器助手类
- [DuckPhp\Helper\ServiceHelper](ref/Helper-ServiceHelper.md) 服务助手类
- [DuckPhp\Helper\ModelHelper](ref/Helper-ModelHelper.md) 模型助手类
- [DuckPhp\Helper\ViewHelper](ref/Helper-ViewHelper.md) 视图助手类
- *[DuckPhp\Helper\AppHelper](ref/Helper-AppHelper.md)* 应用助手类，一般不常用。
- *[DuckPhp\Helper\HelperTrait](ref/Helper-HelperTrait.md)*  助手类公共 Trait。其他助手类都实现这里的方法。

## 开始
助手类是应用开发者必须掌握的类。
App 类包含助手类的全部内容。但是不推荐使用 App 类的助手类方法代替助手类。
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

我们搬出架构图。

![arch_full.gv.svg](arch_full.gv.svg)

上图并没有 AppHelper 助手类，那么 AppHelper 类 的存在意义是什么呢？ 答案是用于 App 类的 助手函数参考， 当你要从 App 类里找出复杂的助手类，还不如在 AppHelper 里找。

为什么这个方法在助手类的声明里查不到?

AssignExtendStaticMethod($key, $value = null)
GetExtendStaticMethodList()
__callStatic($name, $arguments)

## ControllerHelper

本页面展示 ContrlloerHelper 方法。 ContrlloerHelper 的方法很多很杂，但掌握了 ContrlloerHelper 方法，基本就掌握了用法 大致分为 【通用杂项】【路由处理】【异常管理】【跳转】【swoole 兼容】 【内容处理】 几块 内容处理和 ViewHelper 基本通用。 ControllerHelper 方法
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
Setting
【配置相关】见 ServiceHelper 的 Setting 介绍
Config
【配置相关】见 ServiceHelper 的 Config 介绍
LoadConfig
【配置相关】见 ServiceHelper 的 LoadConfig 介绍
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
【路由相关】获得当前的路由调用方法，用于权限判断等
setRouteCallingMethod
【路由相关】设置当前的路由调用方法，用于跨方法调用时候 view 修正
getPathInfo()
【路由相关】获得当前的 PATH_INFO
getParameters
【路由相关】获得路由重写相关的数据
Show($data = [], $view = null)
【内容处理】显示视图， 默认为 view/$view.php 的文件， 并会带上页眉页脚
setViewWrapper($head_file = null, $foot_file = null)
【内容处理】设置页眉页脚
assignViewData($key, $value = null)
【内容处理】分配视图变量，另一版本为 assignViewData($assoc);
Pager()
【内容处理】获得分页器对象, 分页器参考 DuckPhp\Ext\Pager。 DuckPHP 只是做了最小的分页器
assignExceptionHandler
【异常处理】分配异常句柄
setMultiExceptionHandler
【异常处理】设置多个异常处理
setDefaultExceptionHandler
【异常处理】设置异常的默认处理
header
【系统替代】 header 函数以兼容命令行模式
setcookie()
【系统替代】 setcookie 函数以兼容命令行模式
exit
【系统替代】 退出函数，以便于接管
SG
【swoole 兼容】 SG()-> 前缀替代 超全局变量做 swoole 兼容， 如 C::SG()->_GET[] , C::SG()->_POST[] 等。

## ServiceHelper

本页面展示 SerivceHelper 方法。 ServiceHelper 用于 Service 层。 只用到了 ServiceHelper
Setting($key)
读取设置,设置默认在 config/setting.php 里， .env 的内容也会加进来
Config($key, $file_basename = 'config')
读取配置，从 config/$file_basename.php 里读取配置
LoadConfig($file_basename)
载入 config/$file_basename.php 的配置段

## ModelHelper

本页面展示 ModelHelper 方法。
ModelHelper 用于 Model 层。 
ModelHelper 只有数据库的三个独特方法。
这几个方法在 ControllerHelper 里没有。
这几个方法不是 DuckPhp\Core\App 里的。
而是由 DuckPhp\App 加载 DuckPhp\Ext\DBManager 后添加的。
如何使用 DB 对象，看数据库部分的介绍。
ModelHelper
DB($tag=null)
获得 DB 数据库对象 ,第 $tag 个配置的数据库对象
DB_W()
获得用于写入的 DB 对象,这是获得第 0 个配置列表里的数据库
DB_R()
获得用于读取的 DB 对象，这是获得第 1 个配置列表里的数据库

## ViewHelper

本页面展示 ViewHelper 方法。 ViewHelper 是在View 里使用。 ViewHelper 默认的方法在 ControllerHelper 里都有。 但是 ViewHelper 不是作为 ControllerHelper 的子集。 ViewHelper
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

## AppHelper

应用 助手的方法

## Helper公用方法

本页面展示 所有助手类共有的方法。
IsDebug()
IsDebug 方法，用于判断平台
IsRealDebug()
IsRealDebug 切莫乱用。用于环境设置为其他。比如线上环境，但是还是要特殊调试的场合。 如果没被修改，这将和 IsDebug() 一致。
Platform()
获得当前所在平台,设置字段里的 duckphp_platform ，用于判断当前是哪台机器等
trace_dump()
打印当前堆栈，类似 debug_print_backtrce(2)
var_dump(...$args)
替代 var_dump 函数，不是debug 模式下，不会现实，安全使用
GetExtendStaticStaticMethodList()
获得当前助手类扩展了什么，这个常用于查看核心代码给助手类加了什么
__callStatic
静态方法已经被扩展, 会有额外代码，
ThrowOn($flag, $message, $code = 0, $exception_class = null)
如果 flag 成立，那么抛出消息为 $message, code为 $code, $exception_class 的异常，如 $exception_class =null ，则默认为 Exception::class 的异常。 ThrowOn($flag, $message, $exception_class = null) 简化版本， $code=0;
Logger()
获得 PSR 日志类
AssignExtendStaticMethod($key, $value = null)
高级函数，一般不要使用
CallExtendStaticMethod($name, $arguments)
高级函数，一般不要使用


## 高级
扩展 助手类。 最直接的方式就是  添加静态方法。

在 AppPluginTrait 里扩展的助手类。