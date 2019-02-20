# SwooleHttpd 是什么

SwooleHttpd 致力于 Swoole 代码和 fpm 平台 代码几乎不用修改就可以双平台运行。
是对 swoole_http_server 类的一个简单封装。
可以让你用 echo 直接输出。

SwooleHttpd 原先来自 PHP 框架DNMVCS。不对外引用其他 PHP 代码，简单可靠。
但是 SwooleHttpd 是设计成几乎和 DNMVCS 无关的Swoole 框架，所以我把他剥离了。

理论上应该是是高性能的

## 特色
直接用 echo 输出。前面说过。

最方便旧代码迁移。超全局变量用 SwooleHttpd::SG()-> 前缀就可以了。 如 $_GET => SwooleHttpd::SG()->_GET

当然， fpm 方式的代码还没那么简单就代替，我们动用 SwooleHttpd::GLOBALS() 代替全局变量 ,&STATICS()代替 静态变量 CLASS_STATICS() 代替类内静态变量

还有对系统函数的封装 SwooleHttpd::header(),SwooleHttpd::setcookie() 等

尤其是 SwooleHttpd::session_start() swoole_http_server 最常碰到的基本问题。   

最后一个没法处理的： require ,include   ，以及重复包含文件导致 函数的重复。

要处理这些，需要动用到 php-parser ， 写个 SwooleHttpd::PHPFile(),或者 SwooleHttpd::require() SwooleHttpd::include() 想解决。不想折腾太大，所以没去折腾。

## 使用方法：
```
composer require dnmvcs/swoolehttpd
```
```php
<?php
$server_options=[
    'port'=>9528，
    'http_handler'=>function(){},
];
\DNMVCS\SwooleHttpd::RunQuickly($options);
```
想要获得当前 的 request ,response 用 SwooleHttpd::Request() ,Response（）；
还记得 _SERVER,_GET,_POST 超全局变量在 swoole 协程下无法使用么。用 SwooleHttpd::SG() 啊

## server_options 的选项

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

        //* websocket 在测试中。未稳定
        'websocket_open_handler'=>null,     // websocket 打开
        'websocket_handler'=>null,          // websocket  处理
        'websocket_exception_handler'=>null,// websocket 异常处理
        'websocket_close_handler'=>null,    // websocket 关闭
];
```
## 三种模式
SwooleHttpd 有三种模式
1. http_handler

    主要模式
    所有url请求都到这个回调处理,。
    这模式和后面两种模式的区别，就是常驻内存,
    with_http_handler_root 打开时，http_handler 返回 false 后继续进入 http_handler 搜索文件运行
2. http_handler_root

    这和 document_root 一样。读取php文件，然后运行的模式。
    注意重复包含类会导致异常.
    with_http_handler_file  打开时 找不到文件会进入 http_handler_file 处理。
3. http_handler_file

    这种模式是把 url 都转向 文件如 index.php 来处理。

## 基本方法
static G($object=null)

    G 函数，协程单例
static RunQuickly($options=[])
    
    入口，等价于 SwooleHttpd::G()->init($options)->run();
init($options=[])

    初始化，这是最经常子类化完成自己功能的方法。
    你可以扩展这个类，添加工程里的其他初始化。
run()

    运行，运行后进入 swoole_http_server
## 常用静态方法
Server()

    获得当前 swoole_server 对象
Request()

    获得当前 swoole_request 对象
Response()

    获得当前 swoole_response 对象
Frame()

    获得当前 frame （websocket 生效 ）  
FD()

    获得当前 fd  （websocket 生效）
IsClosing()

    判断是否是关闭的包 （websocket 生效）
## 系统封装静态方法
对应PHP手册的函数的全局函数的替代，因为相应的同名函数在 Swoole环境下不可用。
特殊函数 system_wrapper_get_providers 介绍了有多少系统替换函数。
所有这些静态方法都是调用动态方法实现，以方便修改

system_wrapper_get_providers

    特殊方法，对外提供本类有的系统封装函数
exit_system($code=0)

    特殊方法，对应  exit() 语法。退出系统，swoole 里，直接 exit 也是可以的。
header(string $string, bool $replace = true , int $http_status_code=0)

    header 函数
setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)

    设置 cookie
set_exception_handler(callable $exception_handler)
    
    异常函数
register_shutdown_function(callable $callback,...$args)
    
    退出关闭函数
session_start(array $options=[])

    开始 session
session_destroy()

    结束 session
session_set_save_handler(\SessionHandlerInterface $handler)

    重设 session_handler
## 超全局变量相关方法
代替超全局变量

SG()
    
    系统超级变量
&GLOBALS($k,$v=null)

    全局变量 global 语法的替代方法
&STATICS($k,$v=null)

    静态变量 static 语法的替代方法
&CLASS_STATICS($class_name,$var_name)

    类内静态变量 static 语法的替代方法

# 高级内容

前面是使用者知道就够的内容，后面是高级内容了
## 简单 HTTP 服务器
SwooleHttpd 用的 trait SwooleHttpd_SimpleHttpd .
单独使用这个 trait 你可以实现一个 http 服务器

    protected function onHttpRun($request,$response){}
    protected function onHttpException($ex){}
    protected function onHttpClean(){}

    public function onRequest($request,$response)
## 简单 websocket 服务器
SwooleHttpd 用的 trait SwooleHttpd_WebSocket .
单独使用这个 trait 你可以实现一个 websocket 服务器

onRequest($request,$response)

    //
onOpen(swoole_websocket_server $server, swoole_http_request $request)

    //
onMessage($server,$frame)

    //
没有 OnClose 。
## 不常用方法
## 协程单例方法
不常用方法，主要提供给 init 前调用

    ReplaceDefaultSingletonHandler()
    getDymicClasses()
    createCoInstance($class,$object)
    forkMasterInstances($classes,$exclude_classes=[])
    resetInstances()

## SwooleException extends \Exception
    404 错误是用 code=404 那个
    没端口会报错
## Swoole404Exception extends SwooleException


SwooleHttpd  重写了 G 函数的实现，使得做到协程单例。

## 预定义宏
    DNMVCS_DNSINGLETON_REPALACER        耦合连接，协程单例，替换默认实现
    DNMVCS_SYSTEM_WRAPPER_INSTALLER     耦合连接，提供系统封装接口，DNMVVS
    DNMVCS_SUPER_GLOBAL_REPALACER       耦合连接，提供SuperGlobal 接口类
    DNMVCS_SWOOLE_INIT                  Swoole 服务器已经初始化的标志
    DNMVCS_SWOOLE_RUNNING               Swoole 服务器已经运行的标志
## DNSingleton
共享 trait，这代码在 DNMVCS 里也有 单例 G 函数
## DNThrowQuickly
共享 trait，这代码在 DNMVCS 里也有 用于快速抛异常
## SwooleCoroutineSingleton
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
## class SwooleContext
    协程单例。Swoole 的运行信息
    public function initHttp($request,$response)
    public function initWebSocket($frame)
    public function cleanUp()
    public function onShutdown()
    public function regShutDown($call_data)
    public function isWebSocketClosing()
## SwooleSuperGlobal
    SwooleSuperGlobal 是 Swoole 下 SuperGlobal 类的实现。
    相关方法，和公开变量，参考见 DNSuperGlobal

	public $is_inited=false;
    public function init()

## SwooleSession
    SwooleSession 是 Swoole 的 session 实现。
    SwooleSession 被 SwooleSuperGlobal 调用， 调用 SwooleSessionHandler ,
    public function setHandler(\SessionHandlerInterface $handler)
    public function _Start(array $options=[])
    public function _Destroy()
    public function writeClose()
    protected function create_sid()

## SwooleSessionHandler implements \SessionHandlerInterface
因为默认的 SessionHandler 不能直接用，这里做文件实现版本的 SessionHandler

如果你要实现自己的 SessionHandler ，用 SwooleServer::session_set_save_handler();替换这个类。
