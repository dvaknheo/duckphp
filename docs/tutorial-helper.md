# 助手类
**重要，本文对业务工程师很重要**

[toc]
## 相关类

从简单到复杂，我们列出助手类。

- [DuckPhp\Helper\ViewHelper](ref/Helper-ViewHelper.md) 视图助手类
- [DuckPhp\Helper\ModelHelper](ref/Helper-ModelHelper.md) 模型助手类
- [DuckPhp\Helper\BusinessHelper](ref/Helper-BusinessHelper.md) 业务助手类
- [DuckPhp\Helper\ControllerHelper](ref/Helper-ControllerHelper.md) 控制器助手类
- *[DuckPhp\Helper\AdvanceHelper](ref/Helper-AdvanceHelper.md)* 应用助手类，一般不常用。

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

* 写 Model 你可能要引入 LazyToChange\Helper\ModelHelper 助手类别名为 M 。
* 写 Business 你可能要引入 LazyToChange\Helper\BusinessHelper 助手类别名为 B 。
* 写 Controller 你可能要引入 LazyToChange\Helper\ControllerHelper 助手类别名为 C 。
* 写 View 你可能要引入 LazyToChange\Helper\ViewHelper 助手类别名为 V 。
* 不能交叉引入其他层级的助手类。如果需要交叉，那么你就是错的。
* 小工程可以用直接使用入口类 LazyToChange\System\App 类 甚至  DuckPhp\DuckPhp 类，这包含了上述类的公用方法。
* App 类包含助手类的全部内容。但是不推荐使用 App 类的助手类方法代替助手类。

工程的命名空间 LazyToChange 是 可调的。比如调整成 LazyToChangeProject ,TheBigOneProject  等。
参见 `$options['namespace']` 选项;

## 小问答

问：为什么这个方法在助手类的声明里查不到?

答：查看相应助手类方法 `GetExtendStaticMethodList()` ，因为 `__callStatic($name, $arguments)` 魔术方法已经被接管。在里面实现。

问：为什么我的结果和这里的结果不同？

答：`核心工程师`可以修改所有方法的实现。

问：为什么有些方法是大写开始，有些方法是小写开始。

答：大写开始的方法是常用方法，小写开始的方法是不常用方法。高级来说，大写开始方法对应一个静态函数。小写方法是对应动态函数。但是他们都可以更改实现。

问：上面调用关系图怎么没有 `AdvanceHelper` 类

答：`AdvanceHelper` 助手类只由`核心工程师`来调用 。当你要从 App 类里找出复杂的助手类，还不如在 AdvanceHelper 里找。Session 管理就用到了 AdvanceHelper 类。

## 全局助手函数

助手类有些通用的方法，用全局函数代替，全局助手函数用于 View 视图里

### 调试函数

_\_is_debug()

    对应 App::IsDebug() 判断是否在调试状态, 默认读取选项 is_debug 和设置字段里的 duckphp_is_debug
_\_is_real_debug()

    对应 App::IsRealDebug() 。 切莫乱用。用于环境设置为其他。比如线上环境，但是还是要特殊调试的场合。 如果没被接管，和 IsDebug() 一致。
_\_platform()

    对应 App::Platform() 获得当前所在平台,默认读取选项和设置字段里的 duckphp_platform，用于判断当前是哪台机器等
_\_trace_dump(...$arg)

    对应 App::TraceDump() 调试状态下，查看当前堆栈，打印当前堆栈，类似 debug_print_backtrce(2)
_\_var_dump(...$arg)

    对应 App::var_dump()调试状态下 Dump 当前变量，替代 var_dump
_\_debug_log(...$arg)

    对应 App::DebugLog($message, array $context = array()) 对应调试状态下 Log 当前变量。
### 显示相关函数
__h()

    对应 App::H(); HTML 编码
__l($str,$args=[])

    对应 App::L(); 语言处理函数，后面的关联数组替换 '{$key}'
__hl($str, $args=[])

    对应 App::Hl(); 对语言处理后进行 HTML 编码
__url($url)

    对应 App::Url(); 获得相对 url 地址
__domain()

    对应 App::Domain(); 获得带协议头的域名
__display($view, $data = null)

    对应 App::Display(); 包含下一个 $view ， 如果 $data = null 则带入所有当前作用域的变量。 否则带入 $data 关联数组的内容。用于嵌套包含视图。


## ViewHelper 视图助手类

ViewHelper 是在视图里使用，默认没有方法。


## ModelHelper 模型助手类

ModelHelper 用于 Model 层。  ModelHelper 有数据库的三个独特方法。

这几个方法在 ControllerHelper 里没有。 如何使用 Db 对象，看数据库部分的介绍。

此外，还有两个快捷方法， SqlForPager  和 SqlForCountSimply 方便分页。

Db($tag=null)

    获得 DB 数据库对象 ,第 $tag 个配置的数据库对象
    Db() 方法也可以用 __db() 全局函数代替
DbForWrite()

    获得用于写入的 DB 对象,这是获得第 0 个配置列表里的数据库
DbForRead()

    获得用于读取的 DB 对象，这是获得第 1 个配置列表里的数据库

SqlForPager($sql, $pageNo, $pageSize = 10)

    分页 limit 的 sql 
SqlForCountSimply($sql)

    简单的把 select ... from 替换成 select count(*) as c from 

## BusinessHelper 业务助手类

BusinessHelper 用于业务层。
### 配置

Config($key, $file_basename = 'config')

    读取配置，从 config/$file_basename.php 里读取配置
LoadConfig($file_basename)

    载入 config/$file_basename.php 的配置段。
Setting($key);

    获得设置，需要打开'user_setting_file'默认设置文件是在  config/setting.php 。
    设置是敏感信息,不存在于版本控制里面。而配置是非敏感。
LoadConfig($key,$basename="config");

    载入配置，Config($key); 获得配置项目。默认配置文件是在  config/config.php 。
### 其他

Cache($replace_object)

    获得缓存管理器
FireEvent($event, ...$args)

    触发事件
XpCall($callback, ...$args)

    包裹callback输出，如果抛出异常则返回异常，否则返回 $callback();
Loger()

    日志对象
## ControllerHelper 控制器的助手类

 ContrlloerHelper 的方法很多很杂，但掌握了 ContrlloerHelper 方法，基本就掌握了使用方法

大致分为 【显示相关】【配置相关】【跳转相关】【路由处理】【异常管理】【跳转】【内容处理】 几块 内容处理和 ViewHelper 基本通用。 ControllerHelper 方法

### 输出内容

显示
H($str)

    __h() HTML 编码
L($str,$args=[])

    __l() 语言处理函数，后面的关联数组替换 '{$key}'
Hl($str, $args=[])

    __hl() 对语言处理后进行 HTML 编码
Json($ret)

    __json() 获得 Json 内容
Url($url)

    __url() 获得相对 url 地址
Domain()

    __domain【内容处理】 获得带协议的域名
### 输出的动作

Show($data = [], $view = null)

    【内容处理】显示视图， 默认为 view/{$view}.php 的文件， 并会带上页眉页脚
Display($view, $data = null)

    __display() 包含下一个 $view ， 如果 $data = null 则带入所有当前作用域的变量。 否则带入 $data 关联数组的内容。Display 用于嵌套包含视图。
setViewHeadFoot($head_file = null, $foot_file = null)

    【内容处理】设置页眉页脚
assignViewData($key, $value = null)

    【内容处理】分配视图变量，另一版本为 assignViewData([$key=>$value]);
DbCloseAll()

    【内容处理】 关闭所有数据库
### 分页

分页器类是通过 DuckPhp\\Component\\Pager 实现的

PageNo($new_value = null)

    获得或设置当前页码
PageSize($new_value = null)

    获得或设置当前每页数据条目
PageHtml($total, $options=[])

    获得分页结果 HTML，这里的 $options 是传递给 Pager 类的选项。
### 配置

Setting

    【配置相关】见 BusinessHelper 的 Setting 介绍
Config

    【配置相关】见 BusinessHelper 的 Config 介绍
LoadConfig

    【配置相关】见 BusinessHelper 的 LoadConfig 介绍
###  事件
FireEvent($event, ...$args)

    【其他】见 BusinessHelper 的 FireEvent 介绍
    
Loger()

    日志对象
### 异常处理
见 异常管理 一节

assignExceptionHandler

    【异常处理】分配异常句柄
setMultiExceptionHandler

    【异常处理】设置多个异常处理
setDefaultExceptionHandler

    【异常处理】设置异常的默认处理
XpCall($callback, ...$args)

    【其他】见 BusinessHelper 的 XpCall 介绍
### 路由

setRouteCallingMethod

    【路由相关】设置当前的路由调用方法，用于跨方法调用时候 view 修正
getRouteCallingMethod

    【路由相关】获得当前的路由调用方法，用于权限判断等
getPathInfo()

    【路由相关】获得当前的 PATH_INFO
getParameters()

    【路由相关】获得路由重写相关的数据
dumpAllRouteHooksAsString()

    Dump 所有路由钩子
### 跳转

ExitRedirect($url, $exit = true)

    【跳转】跳转到站内URL ，$exit 为 true 则附加 exit()
    ExitRedirect 不能跳转到外站，要用 ExitRedirectOutside
ExitRedirectOutside($url, $exit = true)

    【跳转】跳转到站外URL, $exit 为 true 则附加 exit()
ExitRouteTo($url, $exit = true)

    【跳转】跳转到相对 url , $exit 为 true 则附加 exit()
Exit404($exit = true)

    【跳转】报 404，显示后续页面，$exit 为 true 则附加 exit()
ExitJson($ret, $exit = true)

    【跳转】输出 json 结果，$exit 为 true 则附加 exit()


### 输入变量
替代同名 GET / POST /REQUEST /COOKIE 。如果没的话返回 后面的默认值。
注意没有 SESSION（有 App::SESSION） ，这是故意设计成这样的，不希望 \_SESSION 到处飞， _SESSION 应该集中于 SessionBusiness 或 SessionLib 里。

GET($key, $default = null)

    对应 _GET， $_GET[$key] 不存在则返回 $default;
POST($key, $default = null)

    对应 _POST， $_POST[$key] 不存在则返回 $default;
REQUEST($key, $default = null)

    对应 _REQUEST， $_REQUEST[$key] 不存在则返回 $default;
COOKIE($key, $default = null)

    对应 _COOKIE， $_GET[$key] 不存在则返回 $default;
SERVER($key, $default = null)

    对应 SERVER $_GET[$key] 不存在则返回 $default;
### 系统替代

系统替代静态方法和系统函数一样的参数。为了兼容不同平台，如 CLI, workerman,swoole 使用这些函数替代。

header()

    【系统替代】 header 函数以兼容命令行模式
setcookie()

    【系统替代】 setcookie 函数以兼容命令行模式
exit()

    【系统替代】 退出函数，以便于接管

## AdvanceHelper 高级助手类

AdvanceHelper 是 `核心工程师` 才使用的高级助手类。特殊的 Business 会用到。

一部分只是展示了 App 类里有的非主要生命周期流程外的方法。


### 系统替代

AdvanceHelper 的系统替代更全面，包括 session 族函数

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
其他备忘
```
    public static function extendComponents($method_map, $components = [])
    {
        return App::G()->extendComponents($method_map, $components);
    }
    public static function cloneHelpers($new_namespace, $componentClassMap = [])
    {
        return App::G()->cloneHelpers($new_namespace, $componentClassMap);
    }
    public static function addBeforeShowHandler($handler)
    {
        return App::G()->addBeforeShowHandler($handler);
    }
    ////
    public static function getDynamicComponentClasses()
    {
        return App::G()->getDynamicComponentClasses();
    }
    public static function addDynamicComponentClass($class)
    {
        return App::G()->addDynamicComponentClass($class);
    }
```
## 助手类的公用静态方法

所有助手类都有的静态方法。
GetExtendStaticStaticMethodList()

    用来查看当前类有什么额外的静态方法。
\_\_callStatic

    静态方法已经被扩展。
AssignExtendStaticMethod($key, $value = null)

    高级函数
CallExtendStaticMethod($name, $arguments)

    高级函数
## 其他 DuckPhp 类自带的非助手函数静态方法

这里顺带介绍 DuckPhp 的非助手函数静态方法。 这些函数都是内部调用。

Blank()

    空函数，用于可能的特殊场合
On404

    404 处理函数
OnDefaultException

    默认异常处理函数
OnDevErrorHandler

    默认Notice等错误处理函数
RunQuickly

    重点，快速运行
system_wrapper_replace

    替换系统默认同名函数
system_wrapper_get_providers

    获得系统默认同名函数

## 高级话题：添加或修改助手类的方法 

扩展 助手类。 最直接的方式就是  添加静态方法。

一般工程，都会自 DuckPhp 的助手类 扩展自己的助手类，而不是直接使用 DuckPhp 的助手类。

类似 自己对 ModelHelper 扩展:

```php
<?php
namespace LazyToChange\Helper;

use DuckPhp\Helper\ModelHelper as Helper;

class ModelHelper extends Helper
{
    // override or add your code here
}

```
如果你要修改相关实现，了解 DuckPhp 系统架构后后 参考 [DuckPhp\Core\App](ref/Core-App.md)
如果你自己添加了 Ext 扩展类，那么你需要 `extendComponents` 方法注入相关 Helper
如果你只是替换系统的实现， 找出那些 Helper 的实现函数，替换之。