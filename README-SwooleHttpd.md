# SwooleHttpd

## SwooleHttpd 是什么

SwooleHttpd 致力于 Swoole 代码和 fpm 平台 代码几乎不用修改就可以双平台运行。
是对 swoole_http_server 类的一个包裹。

SwooleHttpd 原先来自 PHP 框架DNMVCS。不对外引用其他 PHP 代码，简单可靠。
但是 SwooleHttpd 是设计成几乎和 DNMVCS 无关的Swoole 框架，所以我把他剥离了。

理论上应该是是高性能的

## 特色

直接用 echo 输出。

最方便旧代码迁移。超全局变量用 SwooleHttpd::SG()-> 前缀就可以了。 如 $_GET => SwooleHttpd::SG()->_GET

当然， fpm 方式的代码还没那么简单就代替，我们动用 SwooleHttpd::GLOBALS() 代替全局变量 ,SwooleHttpd::STATICS()代替 静态变量 SwooleHttpd::CLASS_STATICS() 代替类内静态变量

还有对系统函数的封装 SwooleHttpd::header(),SwooleHttpd::setcookie() 等。

尤其是 SwooleHttpd::session_start() swoole_http_server 最常碰到的基本问题。

最后一个没法处理的： require ,include   ，以及重复包含文件导致 函数的重复。

要处理这些，需要动用到 php-parser ， 写个 SwooleHttpd::PHPFile(),或者 SwooleHttpd::require() SwooleHttpd::include() 想解决。不想折腾太大，所以没去折腾。

## 基本应用

### 使用方法：

```shell
composer require dnmvcs/swoolehttpd
```

```php
<?php
use DNMVCS\SwooleHttpd;
require(__DIR__.'/../autoload.php');
function hello()
{
    echo "<h1> hello ,have a good start.</h1><pre>\n";
    var_export(SwooleHttpd::SG());
    echo "</pre>";
    return true;
}

$options=[
    'port'=>9528,
    'http_handler'=>'hello',
];
SwooleHttpd::RunQuickly($options);
```

浏览器打开 http://127.0.0.1:9528/
这个例子展现了 $_SERVER 里有的东西

### 选项

RunQuickly 的 默认选项 SwooleHttpd::DEFAULT_OPTIONS 有


```php
const DEFAULT_OPTIONS=[
        'swoole_server'=>null,          // swoole_http_server 对象，留空，则用 host,port 创建
        'swoole_options'=>[],           // swoole_http_server 的配置，合并入 swoole_server
        
        'host'=>'0.0.0.0',              // IP
        'port'=>0,                      // 端口
        
        'http_handler'=>null,           // 启动方法，返回 false 表示 404
        'http_handler_basepath'=>'',    // 基础目录目录 ，搭配用于配置 http_handler_root ，http_handler_file
        'http_handler_root'=>'',        // PHP 目录模式。
        'http_handler_file'=>'',        // 映射所有 URI 到单一文件模式
        'http_exception_handler'=>null, // 异常处理回调, set_exception_handler 会覆盖这个配置
        'http_404_handler'=>null,       // 404 的处理回调

        'with_http_handler_root'=>false,// 复用 http_handler_root 404 后会从目录里载入
        'with_http_handler_file'=>false,// 复用 http_handler_root 404 后会从文件里载入

        'enable_fix_index'=>true,       // http_handler 模式下，修正 index.php 为空
        'enable_path_info'=>true,       // http_handler_root 允许 path_info
        'enable_not_php_file'=>true,    // http_handler_root 允许包含资源文件
        
        'base_class'=>null,             // 替换 SwooleHttpd 类初始化
        'silent_mode'=>false,           // 安静模式，不在命令行中提示服务启动信息。
        'enable_coroutine'=>true,       // 启用 \Swoole\Runtime::enableCoroutine();
];
```

### 难度级别

从难度低到高，大概是这样的级别以实现目的 *DNMVCS 通用*

1. 使用默认选项实现目的
2. 只改选项实现目的
3. 调用 SwooleHttpd 类的静态方法实现目的
4. 调用 SwooleHttpd 类的动态方法实现目的
5. ---- 初级程序员和高级程序员分界线 ----
6. 使用入口类扩展
7. 调用扩展类，组件类的动态方法实现目的
8. 继承接管特定类实现目的
9. 魔改，硬改 SwooleHttpd 的代码实现目的

### 文档小备注

（DNMVCS 通用）的备注，在 DNMVCS 中也会有类似的做法。两者文档重复，方便看过 DNMVCS 的人。

### 三种模式

SwooleHttpd 有三种模式

1. http_handler

    主要模式
    所有url请求都到这个回调处理。
    这模式和后面两种模式的区别，就是不搜索文件
    with_http_handler_root 打开时，http_handler 返回 false 后继续进入 http_handler 搜索文件运行
2. http_handler_root

    这和 document_root 一样。读取php文件，然后运行的模式。
    注意重复包含类会导致异常.
    with_http_handler_file  打开时 找不到文件会进入 http_handler_file 处理。
    enable_not_php_file 允许读取资源文件，如图片，将会在浏览器显示图片。
3. http_handler_file

    这种模式是把 url 都转向 文件如 index.php 来处理。

### 常用静态方法

常用静态方法，基本都要用到的静态方法

static RunQuickly(array $options=[],callable $after_init=null) *DNMVCS 通用*

    入口，等价于 SwooleHttpd::G()->init($options)->run();
    如果 after_init不为 null 将会在 init 后执行
ThrowOn($flag,$message,$code=0) *DNMVCS 通用*

    如果 flag 成立抛出异常
    和 DNMVCS 不同的是，这里抛出 SwooleException。
Throw404()

    抛出 Swoole404Exception,进入 404 处理。
Server()

    获得当前 swoole_server 对象
Request()

    获得当前 swoole_request 对象
    返回 SwooleContext::G()->request
Response()

    获得当前 swoole_response 对象
    返回 SwooleContext::G()->response
OnShow404()

    处理404的通用方法，选项 http_404_handler 优先使用
OnException($ex)

    异常的处理方法，选项 http_exception_handler 优先使用

### 超全局变量静态方法 *DNMVCS 通用*

代替超全局变量，基本由 SwooleSuperGlobal 的动态方法实现
高级程序员可以由接管 SwooleSuperGlobal 以实现自己的解决方式。

SG()

    代替系统超级变量
    实质返回 SwooleSuperGlobal::G();
&GLOBALS($k,$v=null)

    全局变量 global 语法的替代方法
    返回 SwooleSuperGlobal::G()->STATICS($k,$v)
&STATICS($k,$v=null)

    静态变量 static 语法的替代方法
    返回 SwooleSuperGlobal::G()->_STATICS($k,$v)
&CLASS_STATICS($class_name,$var_name)

    类内静态变量 static 语法的替代方法
    $class_name 传入类名，以确定是 self::class 还是 static::class
    返回 SwooleSuperGlobal::G()->_CLASS_STATICS($class_name,$var_name)

### 系统封装静态方法

对应PHP手册的函数的全局函数的替代，因为相应的同名函数在 Swoole环境下不可用。
特殊函数 system_wrapper_get_providers 介绍了有多少系统替换函数。
所有这些静态方法都是调用动态方法实现，以方便修改。

system_wrapper_get_providers

    特殊方法，对外提供本类有的系统封装函数
exit_system($code=0)

    特殊方法，对应  exit() 语法。退出系统，swoole 里，直接 exit 也是可以的。
header(string $string, bool $replace = true , int $http_status_code=0)

    header 函数
setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)

    设置 cookie
set_exception_handler(callable $exception_handler)

    设置异常函数
register_shutdown_function(callable $callback,...$args)

    退出关闭函数
session_start(array $options=[])

    开始 session
session_destroy()

    结束 session
session_set_save_handler(\SessionHandlerInterface $handler)

    设置 session_handler

### 高级静态方法

这些静态方法，初学者可以忽略

static G($object=null) *DNMVCS 通用*

    G 函数，可替换单例。

__callStatic($name, $arguments) *DNMVCS 通用*

    SwooleHttpd::G($object) 后 $object 的静态方法 SwooleHttpd 也可用
    SwooleHttpd::G()->assignStaticMethod 定的 静态方法 SwooleHttpd 也可用。

ReplaceDefaultSingletonHandler()

    替换单例实现
    实质返回 SwooleCoroutineSingleton::ReplaceDefaultSingletonHandler();
EnableCurrentCoSingleton()

    开启协程内单例，比如 \go 函数里你需要用到自己的协程单例。
    实质返回 SwooleCoroutineSingleton::EnableCurrentCoSingleton();

### 单例模式

SwooleHttpd  use trait DNSingleton 。
DNSingleton 定义了静态函数 G($object=null)   ，如果默认参数的话得到当前单例。
如果传入  $object 则替换单例，实现调用方式不变，实现方式改变的效果。

SwooleHttpd::  通过使用 SwooleCoroutineSingleton 进一步扩展了 DNSingltone （ 通过 DNMVCS_DNSINGLETON_REPALACER 宏 ）
实现了协程内单例。

如果协程内没单例，会查找全局的单例（$cid=0）的

在协程结束时候，会自动清理所有协程单例。

### 超全局变量和语法代替静态方法

swoole 的协程使得 跨领域的 global ,static, 类内 static 变量不可用，
我们用替代方法

```php
<?php
use DNMVCS\SwooleHttpd as DN;
require (__DIR__.'/../autoload.php');

global $n;
// =>
$n=&DN::GLOBALS('n');

static $n;
// =>
$n=&DN::STATICS('n');  //别漏掉了 &

$n++;
var_dump($n);

class B
{
    protected static $var=10;
    public static function foo()
    {
        //static::$var++;
        //var_dump(static::$var);
        $_=&DN::CLASS_STATICS(static::class,'var');$_   ++;
        // 把 static::$var 替换成  $_=&DN::CLASS_STATICS(static::class,'var');$_
        //别漏掉了 &
        var_dump(DN::CLASS_STATICS(static::class,'var')); // 没等号或 ++ -- 之类非左值不用 &
    }
}
class C extends B
{
    protected static $var=100;
}
C::foo();C::foo();C::foo();
```

输出

```text
int(1)
int(101)
int(102)
int(103)
```

## 高级内容

前面是使用者知道就够的内容，后面是高级内容了

### SwooleHttpd 的其他对外动态方法

init($options=[])

    初始化，这是最经常子类化完成自己功能的方法。
    你可以扩展这个类，添加工程里的其他初始化。
run()

    运行，运行后进入 swoole_http_server
set_http_exception_handler($ex)

    设置异常
exit_request($code=0)

    退出当前请求，等同于 exit
getDynamicClasses()

    获取动态类 http_handler 模式

forkMasterInstances($classes,$exclude_classes=[])

    把master 实例clone 到当前协程。exclude_classes 表示，如果是 克隆的实例有当前的类，则跳过不克隆
resetInstances()

    重置 协程为0 的实例覆盖到当前协程，用空的实例，而不是原有实例。

### SwooleHttpd 的预定义宏

    DNMVCS_DNSINGLETON_REPALACER        耦合连接，协程单例，替换默认实现
    DNMVCS_SYSTEM_WRAPPER_INSTALLER     耦合连接，提供系统封装接口，DNMVVS
    DNMVCS_SUPER_GLOBAL_REPALACER       耦合连接，提供SuperGlobal 接口类

### 简单 HTTP 服务器

SwooleHttpd 用的 trait SwooleHttpd_SimpleHttpd .
单独使用这个 trait 你可以实现一个 http 服务器

    protected function onHttpRun($request,$response){}
    protected function onHttpException($ex){}
    protected function onHttpClean(){}

    public function onRequest($request,$response)
    初始化 SwooleContext 和一些处理。

### 协程单例方法

不常用方法，主要提供给 init 前调用

    getDymicClasses()
    createCoInstance($class,$object)
    forkMasterInstances($classes,$exclude_classes=[])
    resetInstances()

### SwooleException extends \Exception

    404 错误是用 code=404 那个
    没端口会报错

### Swoole404Exception extends \Exception

### SwooleSingleton

共享 trait，这代码在 DNMVCS 里也有 单例 G 函数

SwooleHttpd  重写了 G 函数的实现，使得做到协程单例。

### class SwooleCoroutineSingleton

    用于协程单例,把主进程单例复制到协程单例

    public static function ReplaceDefaultSingletonHandler()
    public static function SingletonInstance($class,$object)
    public static function GetInstance($cid,$class)
    public static function SetInstance($cid,$class,$object)
    public static function DumpString()
    public function cleanUp()
    public function forkMasterInstances($classes,$exclude_classes=[])
    public function forkAllMasterClasses()
    public function _DumpString()
    public static function Dump()

### class SwooleContext

    协程单例。Swoole 的运行信息
    public function initHttp($request,$response)
    public function initWebSocket($frame)
    public function cleanUp()
    public function onShutdown()
    public function regShutDown($call_data)
    public function isWebSocketClosing()

### class SwooleSuperGlobal

    SwooleSuperGlobal 是 Swoole 下 超全局变量 的实现。
    同时处理 session
    调用 SwooleSessionHandler ,

    public $is_inited=false;
    public function init()
    public function &_GLOBALS($k, $v=null)
    public function &_STATICS($name, $value=null, $parent=0)
    public function &_CLASS_STATICS($class_name, $var_name)
    public function session_set_save_handler($handler)
    public function session_start(array $options=[])
    public function session_id($session_id=null)
    public function session_destroy()
    public function writeClose()
    public function create_sid()

### SwooleSessionHandler implements \SessionHandlerInterface

因为默认的 SessionHandler 不能直接用，这里做文件实现版本的 SessionHandler 。

如果你有自己的 SessionHandler ，用 SwooleServer::session_set_save_handler() 安装;

## 代码解读

### 基本流程 init()

    开始检测是否有 base_class ，如果有，则替换当前单例为 base_class 的实现，
    返回 base_class 的 G 实例的 init

    载入选项 如果没有 server 对象则根据配置创建一个。

    SwooleCoroutineSingleton::ReplaceDefaultSingletonHandler(); 替换单例
    宏 DNMVCS_SUPER_GLOBAL_REPALACER 定为 SwooleSuperGlobal::G
    宏 DNMVCS_SYSTEM_WRAPPER_INSTALLER 定为 static::system_wrapper_get_providers;

### 基本流程 run()

    如果不是安静模式，则打印相关信息
    $this->server->start();

### 基本流程 onRequest()

    onRequest 实现于 trait SwooleHttpd_SimpleHttpd
    trait SwooleHttpd_SimpleHttpd （估计现实也没人会用到 SwooleHttpd_SimpleHttpd 而不用 SwooleHttpd ）
    一开始就 defer 手动 gc
    SwooleCoroutineSingleton::EnableCurrentCoSingleton 开启 onRequest 协程的协程单例

    defer 配合 ob_start 处理直接 echo 输出

    SwooleContext 初始化
    SwooleSuperGlobal::G 初始化

    注意代码 SwooleSuperGlobal::G(new SwooleSuperGlobal());
    为什么不是 SwooleSuperGlobal::G()；
    因为要确保 SwooleSuperGlobal::G() 得到的单例是 协程内的单例。

    接下来 正常流程  onHttpRun 处理 http 业务
    出异常则 onHttpException  处理异常。

    流程结束后，进入前面 defer 流程里处理善后
    包括 伪 regist_shutdown_function  处理
    其他信息则  onHttpClean 处理
    SwooleContext 善后处理
    关闭 response;
    （这个 defer 折腾了一段时间处理顺序，没 bug 就暂时不要动了。）

### 基本流程 onHttpClean()

    SwooleHttpd onHttpClean
    处理 autoload ，防止 http_handler_root/http_handler_file 模式多次载入 spl_autoload

### 基本流程 onHttpException($ex)

    这个很简单
    如果是 \Swoole\ExitException 异常， 不用处理
    如果是  Swoole404Exception 则 static::OnShow404();
    否则 static::OnException($ex);

### 基本流程 onHttpRun

    主要流程。
    保存 spl_autoload_functions

如果 http_handler 模式

    关闭自动清理 autoload
    处理选项  enable_fix_index
    运行 http_handler
    如果得到的是 false 而且非 with_http_handler_root，非 http_handler_file 则404
    否则打开自动清理 autoload，继续

如果 http_handler_root 模式

如果 http_handler_file 模式

### DNMVCS handler 相关。

## WebSocket(测试中)

### 配置

```php
        //* websocket 在测试中。未稳定
        'websocket_open_handler'=>null,     // websocket 打开
        'websocket_handler'=>null,          // websocket  处理
        'websocket_exception_handler'=>null,// websocket 异常处理
        'websocket_close_handler'=>null,    // websocket 关闭
```

### 静态方法

Frame()

    获得当前 frame （websocket 生效 ）  
FD()

    获得当前 fd  （websocket 生效）
IsClosing()

    判断是否是关闭的包 （websocket 生效）

### 简单 websocket 服务器

SwooleHttpd 用的 trait SwooleHttpd_WebSocket .
单独使用这个 trait 你可以实现一个 websocket 服务器

onRequest($request,$response)

    //
onOpen(swoole_websocket_server $server, swoole_http_request $request)

    //
onMessage($server,$frame)

    //
没有 OnClose 。
