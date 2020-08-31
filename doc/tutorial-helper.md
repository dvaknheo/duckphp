# 助手类
**重要，本文对业务工程师很重要**

[toc]
## 相关类
- *[DuckPhp\Helper\HelperTrait](ref/Helper-HelperTrait.md)*  助手类公共 Trait。其他助手类都实现这里的方法。
- [DuckPhp\Helper\ViewHelper](ref/Helper-ViewHelper.md) 视图助手类
- [DuckPhp\Helper\ModelHelper](ref/Helper-ModelHelper.md) 模型助手类
- [DuckPhp\Helper\ControllerHelper](ref/Helper-ControllerHelper.md) 控制器助手类
- [DuckPhp\Helper\BusinessHelper](ref/Helper-BusinessHelper.md) 服务助手类
- *[DuckPhp\Helper\AppHelper](ref/Helper-AppHelper.md)* 应用助手类，一般不常用。

## 开始
助手类是 `业务工程师` 必须掌握的类。
我们搬出架构图。

![arch_full.gv.svg](arch_full.gv.svg)
文字版

```text
           /-> View-->ViewHelper
Controller --> Business ------------------------------ ---> Model
         \         \   \               \  /                  \
          \         \   \-> (Business)Lib ----> ExModel----------->ModelHelper
           \         \             \                
            \         ---------------->BusinessHelper
             \-->ControllerHelper
```
上述构架图省略了命名空间。



作为 `业务工程师` ， 你不能引入 DuckPhp 的任何东西，就当 DuckPhp 命名空间不存在。
核心工程师才去研究 DuckPhp 类的东西。

* 写 Model 你可能要引入 MY\Base\Helper\ModelHelper 助手类别名为 M 。
* 写 Business 你可能要引入 MY\Base\Helper\BusinessHelper 助手类别名为 B 。
* 写 Controller 你可能要引入 MY\Base\Helper\ControllerHelper 助手类别名为 C 。
* 写 View 你可能要引入 MY\Base\Helper\ViewHelper 助手类别名为 V 。
* 不能交叉引入其他层级的助手类。如果需要交叉，那么你就是错的。
* 小工程可以用直接使用入口类 MY\Base\App 类，这包含了上述类的公用方法。
* App 类包含助手类的全部内容。但是不推荐使用 App 类的助手类方法代替助手类。

工程的命名空间 MY 是 可调的。比如调整成 MyProject ,TheBigOneProject  等。
参见 $options['namespace'];

## 小问答

问：为什么这个方法在助手类的声明里查不到?

答：查看相应助手类方法 GetExtendStaticMethodList() ，因为 \_\_callStatic($name, $arguments) 已经被接管。在里面实现。

问：为什么我的结果和这里的结果不同？

答：`核心工程师`可以修改所有方法的实现。

问：为什么有些方法是大写开始，有些方法是小写开始。

答：大写开始的方法是常用方法，小写开始的方法是不常用方法。高级来说，大写开始方法对应一个静态函数。小写方法是对应动态函数。但是他们都可以更改实现。

问：上面调用关系图怎么没有 `AppHelper` 类

答：`AppHelper` 助手类只由`核心工程师`来调用 。当你要从 App 类里找出复杂的助手类，还不如在 AppHelper 里找。Session 管理就用到了 AppHelper 类。



## 助手类的公用静态方法

所有助手类都有的静态方法。


ThrowOn($flag,$messsage,$code=0,$exception_class=null)

    抛异常 如果 flag 成立，抛出 $exception_class(默认为 \Exception 类);
GetExtendStaticStaticMethodList()

    用来查看当前类有什么额外的静态方法。
\_\_callStatic

    静态方法已经被扩展。
IsDebug()

    判断是否在调试状态, 默认读取选项和设置字段里的 duckphp_is_debug
IsRealDebug()

    IsRealDebug 切莫乱用。用于环境设置为其他。比如线上环境，但是还是要特殊调试的场合。 如果没被接管，和 IsDebug() 一致。
Platform()

    获得当前所在平台,默认读取选项和设置字段里的 duckphp_platform，用于判断当前是哪台机器等
Logger($object = null)

    获得或设置 psr 标准的 Logger 类。默认是 DuckPhp\Core\Logger 类。
trace_dump()

    调试状态下，查看当前堆栈，打印当前堆栈，类似 debug_print_backtrce(2)
var_dump(...$arg)

    调试状态下 Dump 当前变量，替代 var_dump
GetExtendStaticStaticMethodList()

    获得当前助手类扩展了什么，这个常用于查看核心代码给助手类加了什么
ThrowOn($flag, $message, $code = 0, $exception_class = null)

    如果 flag 成立，那么抛出消息为 $message, code为 $code, $exception_class 的异常，如 $exception_class =null ，则默认为 Exception::class 的异常。
    另一调用方式：ThrowOn($flag, $message, $exception_class = null)  相当于 $code=0;
    参见 trait Duckphp\\Core\\ThrowOn
AssignExtendStaticMethod($key, $value = null)

    高级函数
CallExtendStaticMethod($name, $arguments)

    高级函数

## ViewHelper 视图助手类

本页面展示 ViewHelper 方法。 ViewHelper 是在View 里使用。 ViewHelper 默认的方法在 ControllerHelper 里都有。 但是 ViewHelper 不是 ControllerHelper 的子集。


H($str)

	HTML 编码
L($str,$args=[])

	语言处理函数，后面的关联数组替换 '{$key}'
HL($str, $args=[])

	对语言处理后进行 HTML 编码
URL($url)

	获得相对 url 地址
Display($view, $data = null)

	包含下一个 $view ， 如果 $data =null 则带入所有当前作用域的变量。 否则带入 $data 关联数组的内容

## BusinessHelper 业务的助手类

 BusinessHelper 用于业务层。



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
ModelHelper 有数据库的三个独特方法。
这几个方法在 ControllerHelper 里没有。
如何使用 DB 对象，看数据库部分的介绍。
此外，还有两个快捷方法，方便分页

DB($tag=null)

    获得 DB 数据库对象 ,第 $tag 个配置的数据库对象
DB_W()

    获得用于写入的 DB 对象,这是获得第 0 个配置列表里的数据库
DB_R()

    获得用于读取的 DB 对象，这是获得第 1 个配置列表里的数据库

SqlForPager($sql, $pageNo, $pageSize = 10)

    分页 limte 的 sql 
SqlForCountSimply($sql)
    
    简单的把 select ... from 替换成select count(*)as c from 

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
Display

    【显示相关】见 ViewHelper 的 Display 介绍
### 配置相关
Setting

    【配置相关】见 BusinessHelper 的 Setting 介绍
Config

    【配置相关】见 BusinessHelper 的 Config 介绍
LoadConfig

    【配置相关】见 BusinessHelper 的 LoadConfig 介绍
### 跳转相关
ExitRedirect 不能跳转到外站，要用 ExitRedirectOutside

ExitRedirect($url, $exit = true)

    【跳转】跳转到站内URL ，$exit 为 true 则附加 exit()
ExitRedirectOutside($url, $exit = true)

    【跳转】跳转到站外URL, $exit 为 true 则附加 exit()
ExitRouteTo($url, $exit = true)

    【跳转】跳转到相对 url , $exit 为 true 则附加 exit()
Exit404($exit = true)

    【跳转】报 404，显示后续页面，$exit 为 true 则附加 exit()
ExitJson($ret, $exit = true)

    【跳转】输出 json 结果，$exit 为 true 则附加 exit()
### 路由相关
setRouteCallingMethod

    【路由相关】设置当前的路由调用方法，用于跨方法调用时候 view 修正
getRouteCallingMethod

    【路由相关】获得当前的路由调用方法，用于权限判断等
getPathInfo()

    【路由相关】获得当前的 PATH_INFO
getParameters(): array

    【路由相关】获得路由重写相关的数据
### 内容处理
Show($data = [], $view = null)

    【内容处理】显示视图， 默认为 view/{$view}.php 的文件， 并会带上页眉页脚
setViewHeadFoot($head_file = null, $foot_file = null)

    【内容处理】设置页眉页脚
assignViewData($key, $value = null)

    【内容处理】分配视图变量，另一版本为 assignViewData([$key=>$value]);
### 异常处理
见 异常管理 一节

assignExceptionHandler

    【异常处理】分配异常句柄
setMultiExceptionHandler

    【异常处理】设置多个异常处理
setDefaultExceptionHandler

    【异常处理】设置异常的默认处理
### 系统替代
header

    【系统替代】 header 函数以兼容命令行模式
setcookie()

    【系统替代】 setcookie 函数以兼容命令行模式
exit

    【系统替代】 退出函数，以便于接管
SG

    【swoole 兼容】 SG()-> 前缀替代 超全局变量做 swoole 兼容， 如 C::SG()->_GET[] , C::SG()->_POST[] 等。
### 输入相关
替代同名 GET / POST /REQUEST /COOKIE 。如果没的话返回 后面的默认值。
注意没有 \_SESSION ，这是故意设计成这样的，不希望 \_SESSION 到处飞，\ _SESSION 应该集中于 SessionService 或 SessionLib 里。

ENV 也是不希望人用所以没有。

GET($key, $default = null)

    对应 _GET， $_GET[$key] 不存在则返回 $default;
POST($key, $default = null)

    对应 _POST， $_POST[$key] 不存在则返回 $default;
REQUEST($key, $default = null)

    对应 _REQUEST， $_REQUEST[$key] 不存在则返回 $default;
COOKIE($key, $default = null)

    对应 _COOKIE， $_GET[$key] 不存在则返回 $default;
SEVER($key, $default = null)

    对应 SEVER $_GET[$key] 不存在则返回 $default;
### 分页

分页器类是通过 DuckPhp\\Ext\\Pager 实现的

Pager()

    获得分页器对象, 分页器参考 DuckPhp\Ext\Pager。 DuckPhp 只是做了最小的分页器
PageNo(new_value = null)

    获得或设置当前页码
PageSize($new_value = null)

    获得或设置当前每页数据条目
PageHtml($total, $options=[])

    获得分页结果 HTML，这里的 $options 的传递给 Pager 类的选项。

## AppHelper

应用 助手的方法
### 系统替代

AppHelper 的系统替代更全面，包括 session 族函数

header

    【系统替代】 header 函数以兼容命令行模式
setcookie()

    【系统替代】 setcookie 函数以兼容命令行模式
exit

    【系统替代】 退出函数，以便于接管
set_exception_handler(callable $exception_handler)

    【系统替代】 用于 swoole 中特殊用处
register_shutdown_function(callable $callback, ...$args)

    【系统替代】 用于 swoole 中特殊用处
session_start(array $options = [])

    【系统替代】
session_id($session_id = null)
    【系统替代】
session_destroy()

    【系统替代】
session_set_save_handler(\SessionHandlerInterface $handler)

    【系统替代】
### 常用操作

isRunning()

    判断是否在运行状态
isInException()

    判断是否在异常中
addRouteHook($hook, $position, $once = true)

    给路由添加钩子，见相关文档
setUrlHandler($callback)

    实现自己的 URL 函数
assignPathNamespace($path, $namespace = null)

    自动载入
CallException($ex)

    调用异常处理，一般也不用，而是看异常处理那章
### Swoole 兼容

这是 Swoole 开发才会用到的方法。这里暂时不解释

&GLOBALS($k, $v = null)

    替换全局变量
&STATICS($k, $v = null, $_level = 1)

    替换静态变量
&CLASS_STATICS($class_name, $var_name)

    替换类内静态变量
## 高级话题：添加或修改助手类的方法 

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
如果你要修改相关实现，了解 DuckPhp 系统架构后后 参考 [DuckPhp\Core\App](ref/Core-App.md)
如果你自己添加了 Ext 扩展类，那么你需要 extendComponents 方法注入相关 Helper
如果你只是替换系统的实现， 找出那些 Helper 的实现函数，替换之。