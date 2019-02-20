
# SwooleSimpleHttpd 是什么
SwooleSimpleHttpd 致力于 Swoole 代码和 fpm 平台 代码几乎不用修改就可以双平台运行。
是对 swoole_http_server 的一个简单封装。
可以让你用 echo 直接输出。
原先来自 PHP 框架DNMVCS。不对外引用其他 PHP 代码，可靠。是设计成几乎和 DNMVCS 无关的Swoole 框架。

## 三种模式
如果 http_handler 为空，有 http_handler_file 则直接 include  http_handler_file 运行，和 DNMVCS 系统无关

SwooleSimpleHttpd 有三种模式
1. http_handler_root
	这和 document_root 一样。读取php文件，然后运行的模式。
	注意重复包含类会导致异常
2. http_handler_file
	这种模式是把 url 都转向 文件如 index.php 来处理。
3. http_handler
	所有url请求都到这个函数处理,主要模式。
	这模式和上面两种模式的区别，就是常驻内存,
## 使用方法：
```
composer require dnmvcs/swoolesimplehttpd
```
```php
<?php
$server_options=[
	'port'=>9528，
];
\DNMVCS\SwooleSimpleHttpd::init($server_options)->run();
```


SwooleHttpServer  重写了 G 函数的实现，使得做到协程单例。
想要获得当前 的 request ,response 用 SwooleSimpleHttpd::Request() ,Response（）；
还记得 _SERVER,_GET,_POST 超全局变量在 swoole 协程下无法使用么。用 SwooleSimpleHttpd::SG() 啊



## server_options 的选项

```php
const DEFAULT_OPTIONS=[
		'swoole_server'=>null,  // swoole_http_server 对象，留空，则用 host,port 创建
		'swoole_options'=>[],   // swoole_http_server 的配置，合并入 swoole_server
		
		'host'=>'0.0.0.0',      // IP
		'port'=>0,              //端口

		'http_handler_basepath'=>'',	// 基础目录
		'http_handler_root'=>null,      // php 的目录和静态目录的不相同，留空
		'http_handler_file'=>null,      // 启动文件 留空将会使用 http_handler
		'http_handler'=>null,           // 启动方法，
		'http_exception_handler'=>null, // 异常处理方法,DNMVCS 已经占用  // http_handler_root 的异常也是这里处理

		'use_http_handler_root'=>false,	// 复用 http_handler_root 404 后会从目录文件里载入

		//* websocket 在测试中。未稳定
		'websocket_open_handler'=>null,  //websocket 打开
		'websocket_handler'=>null,          //websocket  处理
		'websocket_exception_handler'=>null,    //websocket 异常处理
		'websocket_close_handler'=>null,        //websocket 关闭
];
```
http_exception_handler，用于 单文件模式和目录模式，你可以在这里处理 404。

## 预定义宏
	DNMVCS_DNSINGLETON_REPALACER	 耦合连接，协程单例，替换默认实现
	DNMVCS_SYSTEM_WRAPPER_INSTALLER  耦合连接，提供系统封装接口，DNMVVS
	DN_SWOOLE_SERVER_INIT			Swoole 服务器已经初始化的标志
	DN_SWOOLE_SERVER_RUNNING		Swoole 服务器已经运行的标志
## class SwooleServer
## 基本方法
static G($object=null)
	G 函数，协程单例不多说。
init($options=[])
	初始化，这是最经常子类化完成自己功能的方法。
	你可以扩展这个类，添加工程里的其他初始化。
run()
	运行
## 常用静态方法
Server()

	获得当前 swoole_server 对象
Request()

	获得当前 swoole_request 对象
Response()

	获得当前 swoole_response 对象
Frame()
	获得当前  frame （websocket 生效 ）  
FD()
	获得当前  fd  （websocket 生效）
IsClosing()
	判断是否是关闭的包 （websocket 生效）
## 系统封装静态方法
对应PHP手册的函数的全局函数的替代，因为相应的同名函数在 Swoole环境下不可用。

header(string $string, bool $replace = true , int $http_status_code=0)

	header 函数
setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)

	设置 cookie
exit_system($code=0)

	退出系统，相应的是 exit ，swoole 里，直接 exit 也是可以的。
set_exception_handler(callable $exception_handler)
	
	异常函数
register_shutdown_function(callable $callback,...$args)
	
	退出关闭函数
session_start(array $options=[])
	开始 session
session_destroy()
	结束 session
session_set_save_handler(\SessionHandlerInterface $handler)

	session 函数变更

system_wrapper_get_providers

	特殊方法 ,对外提供本类有的系统封装函数

## 超全局变量相关方法
代替超全局变量

SG()
	
	获得系统超级变量
&GLOBALS($k,$v=null)

	全局变量 global 语法的替代方法
&STATICS($k,$v=null)
	静态变量 static 语法的替代方法
&CLASS_STATICS($class_name,$var_name)
	类内静态变量 static 语法的替代方法

## 协程单例方法
CloneInstance
	把静态单例克隆到当前协程。
ReplaceDefaultSingletonHandler

	不常用方法，主要提供给 init 前调用
## 简单 HTTP 服务器
SwooleHttpServer 用的 trait SwooleHttpServer_SimpleHttpd .
单独使用这个 trait 你可以实现一个 http 服务器

	protected function onHttpRun($request,$response){}
	protected function onHttpException($ex){}
	protected function onHttpClean(){}
	
	public function onRequest($request,$response)
## 简单 websocket 服务器
SwooleHttpServer 用的 trait SwooleHttpServer_WebSocket .
单独使用这个 trait 你可以实现一个 websocket 服务器

onRequest($request,$response)

	//
onOpen(swoole_websocket_server $server, swoole_http_request $request)

	//
onMessage($server,$frame)

	//
没有 OnClose 。
## 不常用方法
public function set_http_exception_handler($exception_handler)
public function exit_request($code=0)
public function show404()

## 高级内容

## DNSingleton
共享 trait，这代码在 DNMVCS 里也有 单例G函数
## DNThrowQuickly
共享 trait，这代码在 DNMVCS 里也有 快速抛异常
## SwooleCoroutineSingleton

用于协程单例,把主进程单例复制到协程单例


	public static function Dump()
	public static function DumpString()
## SwooleException extends \Exception
	404 错误是用 code=404 那个
	没端口会报错
## Swoole404Exception extends SwooleException

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
## SwooleSession
	SwooleSession 是 Swoole 的 session 实现。
	SwooleSession 被 SwooleSuperGlobal 调用， 调用 SwooleSessionHandler ,SwooleHttpServer
	public function setHandler(\SessionHandlerInterface $handler)
	public function _Start(array $options=[])
	public function _Destroy()
	public function writeClose()
	public function writeClose()
	protected function create_sid()

## SwooleSessionHandler implements \SessionHandlerInterface
	因为默认的 SessionHandler 不能直接用，这里做文件实现版本的 SessionHandler
	如果你要实现自己的 SessionHandler ，用 SwooleServer::session_set_save_handler();替换这个类。
