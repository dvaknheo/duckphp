# DNMVCS 教程
## 快速入门
假定不管什么原因，上级选用了 DNMVCS 这个框架，需要快速入门
最快的方式是从 github 下载 DNMVCS。
到所在目录之下运行
```bash
php template/bin/start_server.php
```
浏览器中打开 http://127.0.0.1:8080/ 得到欢迎页 即可。
```text
////
```
OK 了

## 任务

http://127.0.0.1:8080/test/done  显示当前时间的任务。
然后对照目录结构我们要加个 test/done 显示当前时间

### Controller控制器
写 /about/foo 控制器对应的内容

```php
<?php
namespace MY\Controller;
// ::/app/Controller/about.php

class test
{
    public function done()
    {
        $data=[];
        $data['var']=MiscService::G()->foo();
        \DNMVCS\DNMVCS::Show($data);
    }
}

```
### View 视图
先做出要显示的样子。
::/view/about/foo.php
```php
<!doctype html><html><body>
<h1>test</h1>
<div><?=$var ?></div>
</body></html>
```
### Service 服务
业务逻辑层。

::/app/Service/MiscService.php
```php
<?php
class MiscService
{
    use \DNMVCS\DNSingleton;
    public function foo()
    {
        $time=NoDB_MiscModel::G()->getTime();
        $ret='Now is '.$time;
        return $ret;
    }
}
```
### Model 模型

完成 NoDB_MiscModel
Model 类是实现基本功能的
这里用 NoDB_ 表示和没使用到数据库

::/app/Model/NoDB_MiscModel.php
```php
<?php
class NoDB_MiscModel
{
    use \DNMVCS\DNSingleton;
    public function getTime()
    {
        return DATE(DATE_ATOM);
    }
}
```

## 常见任务：URL 地址

如果不是全站 PATH_INFO 模式， web 框架获取某个 URL 地址是常见任务。

DNMVCS::URL($url) 函数就是用于这个任务。

使得你不必关系是用在 /index.php 或者 /somefolder/index.php 里用 PATH_INFO 。

DNMVCS::URL('about/foo') 都会得到正确的 URL 地址。




直接在 github 上下载本项目，
```bash
php template/bin/start_server.php
```


你也可以 composer 安装


```bash
composer require dnmvcs/framework
```
把网站目录指向 public/index.php 就行。

可我不是全站的。甚至，我都没法 path_info —— 没关系，你这个需求是有些诡异但可以解决。

我们接下来会逐步学习：

## 从入门到精通
1. 学习 DNCore 的配置
2. 调用 DNCore 类的静态方法实现目的
3. 调用 DNCore 类的动态方法实现目的
4. 学习 DNMVCS 的配置
4. 调用 DNMVCS 类的静态方法实现目的
5. 调用 DNMVCS 类的动态方法实现目的
6. 学习更高级的调用
7. ---- 核心程序员和高级程序员分界线 ----
8. 扩展 DNMVCS 类
9. 调用扩展类，组件类的动态方法实现目的
10. 继承接管，特定类实现目的
11. 魔改，硬改 DNMVCS 的代码实现目的

## 安装

### composer 安装

```bash
composer require dnmvcs/framework
php bin/start_server.php
```

浏览器中打开 http://127.0.0.1:8080/ 得到欢迎页


然后试着添加例子。



### 目录结构

默认的目录结构

```text
+---app                     // psr-4 标准的自动加载目录
|   +---Base                // 基类放在这里
|   |      App.php          // 默认框架入口文件
|   |      Contrllor.php    // 控制器基类
|   |      Model.php        // 模型基类
|   |      Service.php      // 服务基类
|   +---Controller          // 控制器目录
|   |       Main.php        // 默认控制器
|   +---Model               // 模型放在里
|   |       TestModel.php   // 测试模型
|   \---Service             // 服务目录
|           TestService.php // 测试 Service
+---bin                     // 命令行程序放这里
|       start_server.php    // 启动服务
+---config                  // 配置文件放这里
|       config.php          // 配置，目前是空数组
|       setting.sample.php  // 设置，去除敏感信息的模板
+---headfile                // 头文件处理
|       headfile.php        // 头文件处理
+---view                    // 视图文件放这里，可调
|   |   main.php            // 视图文件
|   \---_sys                // 系统错误视图文件放这里
|           error-404.php   // 404
|           error-500.php   // 500 出错了
|           error-debug.php // 调试的时候显示的视图
|           error-exception.php // 出异常了，和 500 不同是 这里是未处理的异常。
\---public                  // 网站目录约定放这里
        index.php           // 主页
```
这些结构能精简么？
可以，你可以一个目录都不要。

## 第二步，跑 hello world

```php
<?php
require(__DIR__.'/../headfile/headfile.php');  //头文件

$path=realpath(__DIR__.'/..');
$options=[
    'path'=>$path,
    'namespace'=>'MY',
];
if (defined('DNMVCS_WARNING_IN_TEMPLATE')) {
    $options['setting_file_basename']='';
    $options['is_dev']=true;
    echo "<div>Don't run the template file directly </div>\n";
}
\DNMVCS\DNMVCS::RunQuickly($options);
// \DNMVCS\DNMVCS::G()->init($options)->run();
```

## 基本配置
```php
    const DEFAULT_OPTIONS=[
            'path'=>null,
            'namespace'=>'MY',
            'path_namespace'=>'app',
            'skip_app_autoload'=>false,
            
            //// properties ////
            'override_class'=>'Base\App',
            'is_dev'=>false,
            'platform'=>'',
            'path_view'=>'view',
            'path_config'=>'config',
            'skip_view_notice_error'=>true,
            'use_inner_error_view'=>false,
            
            //// config ////
            'setting_file_basename'=>'setting',
            'all_config'=>[],
            'setting'=>[],
            'reload_platform_and_dev'=>true,
            
            //// error handler ////
            'error_404'=>'_sys/error-404',
            'error_500'=>'_sys/error-500',
            'error_exception'=>'_sys/error-exception',
            'error_debug'=>'_sys/error-debug',
            
            //// controller ////
            'controller_base_class'=>null,
            'controller_prefix_post'=>'do_',
                'controller_enable_paramters'=>false,
                'controller_methtod_for_miss'=>null,
                'controller_hide_boot_class'=>false,
                'controller_welcome_class'=>'Main',
                'controller_index_method'=>'index',
        ];
```

这是基础的，后面还有一大堆的配置。
总之，这里很明白了。

    'path'=>null,                   根目录
    'namespace'=>'MY',              命名空间
    'path_namespace'=>'app',        autoload 的命名空间
    'skip_app_autoload'=>false,     如果你有其他加载方式，设置为 false;

    'override_class'=>'Base\App',  这项后面再说

    'is_dev'=>false,                配置是否是在开发状态 * 设置文件的  is_dev 会覆盖
    'platform'=>'',                 配置开发平台 * 设置文件的  platform 会覆盖

    'path_view'=>'view',            视图的目录，基于 path 配置
    'path_config'=>'config',        配置的目录，基于 path 配置

    'skip_view_notice_error'=>true, view 视图里忽略 notice 错误。
    'use_inner_error_view'=>false,  忽略  error_* 配置，使用内部的错误视图

    'setting_file_basename'=>'setting', 如果这项为空，那就不读设置文件了。

    'all_config'=>[],               合并入的 config; // 当你不想读取配置的时候从这里拿
    'setting'=>[],                  合并入的 setting; // 当你不想读取配置的时候从这里拿设置
    'reload_platform_and_dev'=>true,

    'error_404'=>'_sys/error-404',
    'error_500'=>'_sys/error-500',
    'error_exception'=>'_sys/error-exception',
    'error_debug'=>'_sys/error-debug',

#### override_class
注意到 app/Base/App.php 这个文件 MY\Base\App extends DNMVCS\DNMVCS;


## 调参数。

OK 安装好了，用了 路由， URL 也要更改，所以我们要调用 DN::URL 来显示路由。
### 常见任务： 状态

### 常见任务：URL 地址
如果不是全站 PATH_INFO 模式， web 框架获取某个 URL 地址是常见任务。
DNMVCS::URL($url) 函数就是用于这个任务。
使得你不必关系是用在 /index.php 或者 /somefolder/index.php 里用 PATH_INFO 。
DNMVCS::URL('about/foo') 都会得到正确的 URL 地址。

*进阶，接管 URL 函数  .*
### 常见任务：View 和 View 的包含

DNMVCS::Show($data,$view=null) 用于 View 的显示， $view 为空的时候，会根据当前 URL 获得相关 view 文件。
当要在 View 里包含的时候，用 DNMVCS::ShowData($view,$data=null); $data 为 null 的时候，会把当前view 数据带过去。

*进阶，接管 View .*

### 常见任务：读取配置和设置
DNMVCS::Setting($key) 用于读取 config/setting.php 的 $key 。
DNMVCS::Config($key,$basename='config')用于读取 config/$basename.php  $key 。
DNMVCS::LoadConfig($basename='config')用于载入 config/$basename.php 的内容。
设置是敏感信息。而配置是非敏感
*进阶，更多配置和设置相关 .*
### 常见任务： URL 重写
$options['rewrite_map'] 用于重写 url . 以 ~ 开始的表示正则，同时省略 / 必须转义的。 用 $ 代替 \ 捕获。
$options['route_map'] ,用于 回调式路由， 除了  :: 表示类的静态方法，还 -> 符号表示的是类的动态方法。
key  可以加 GET POST 方法。
### 常见任务：重写错误页面

错误页面在 ::view/_sys/ 目录下 里。你可以修改相应的错误页面方法。
比如 404 是 view/404.php 。
你可以更改 DNMVCS 的报错页面。
无错误页面模式，会自己显示默认错误。
你也可以修改 $options['error_404'] 指向一个回调函数来处理 404 错误，其他错误类似。

*进阶 错误管理.*
### 常见任务： 使用数据库
使用数据库，在 DNMVCS 设置里正确设置 database_list 这个数组，包含多个数据库配置
然后在用到的地方调用 DNMVCS::DB($tag=null) 得到的就是 DNDB 对象，用来做各种数据库操作。
$tag 对应 $setting['database_list'][$tag]。默认会得到最前面的 tag 的配置。

你不必担心每次框架初始化会连接数据库。只有第一次调用 DNMVCS::DB() 的时候，才进行数据库类的创建。

DB 的使用方法，看后面的参考。
示例如下

```php
$sql="select 1+? as t";
$ret=\DNMVCS\DNMVCS::DB()->fetch($sql,2);
var_dump($ret);
```

进阶内容

DB 类仅仅是简单的封装 PDO ，作为主程序员，可能要重新调整。
DNMVCS 的默认数据库是 DB ,DB 功能很小，兼容 Medoo 这个数据库类。

你可以用更习惯的类。
### 常见任务： 跳转
* DNMVCS::ExitJson($data) 输出 json 。
* DNMVCS::ExitRedirect($url) 用于 302 跳转。
* DNMVCS::ExitRouteTo($url) 相当于 302 跳转到 DNMVCS::URL($url);
* DNMVCS::Exit404 显示404页面。

### 常见任务： HTML 编码辅助函数
* DNMVCS::H($str)   Html编码. 更专业的有 Zend\Escaper。
* DNMVCS::RecordsetH 对一个 RecordSet 加 html 编码
* DNMVCS::RecordsetURL  对  RecordSet 加 url 转换

*进阶：把 html 编码替换成 Zend\Escaper .*
### 常见任务： 抛异常

```
DNMVCS::ThrowOn($flag,$message,$code);
```

等价于 if(!$flag){throw new DNException($message,$code);}
这是 DNMVCS 应用常见的操作。

### 常见任务： 和其他框架的整合
DNMVCS 整合其他框架：

```php
<?php
    $options['error_404']=function(){};
    $flag=DNMVCS::RunQuickly($options);
    if($flag){ return; }
    // 后面是其他框架代码
```

原理是由其他框架去处理 404。
其他框架整合 DNMVCS ,则在相应处理 404 的地方开始
### 静态函数参考
#### Show
Show($data=[]],$view=null)

    显示视图
    视图的文件在 ::view 目录底下。你可以通过选项 path_view 调整
    为什么数据放前面，DN::Show(get_defined_vars());把 controller 的变量都整合进来，并用默认路径作为 view 文件。
    实质调用 DNView::G()->_Show();
#### ShowBlock
ShowBlock($view,$data=null)

    展示一块 view ，用于 View 里嵌套其他 View 或调试的场合。
    展示view不理会页眉页脚，也不做展示的后处理，如关闭数据库。
    注意这里是 $view 在前面， $data 在后面，和 show 函数不一致哦。
    如果 $data===null 那么会继承上级的 view 数据
    实质调用 DNView::G()->_ShowBlock();
#### URL
URL($url)

    获得调整路由后的url地址 
    当你重写 DNRoute 类后，你可能需要重写这个方法来展示
    比如 simple_route_key 开启后， URL('class/method?foo=bar') 
    将会是 ?r=class/method&foo=bar ，而不是 /class/method?foo=bar
    如果是 / 开始的 URL ，将是从网站根目录开始。

    实质调用 DNRoute::G()->_URL();
#### Parameters
Parameters()

    获得路径切片
    当用正则匹配路由的时候，匹配结果放在这里。
    如果开启了 eanbale_parameter 匹配选项也会在这里。
    这会使得 /about/foo/123/456 路由调用方法为 => about->foo(123,456);

    实质调用 DNRoute::G()->_Parameters();

#### Setting
Setting($key)

    读取设置
    设置在 ::/config/setting.php 里，php 格式
    配置非敏感信息，放在版本管理中，设置是敏感信息，不保存在版本管理中
    实质调用 DNConfig::G()->_Setting();
#### Config
Config($key,$file_basename='config')

    读取配置 
    配置放在 config/$file_basename.php 里，php 格式
    配置是放在非敏感信息，放在版本管理中
    实质调用 DNConfig::G()->_Config();
#### LoadConfig
LoadConfig($file_basename)

    加载其他配置
    如果很多配置文件，手动加载其他配置
    实质调用 DNConfig::G()->LoadConfig();
#### Platform
Platform()

    返回当前环境平台，默认为空默认读设置里的 platform 
#### Developing
Developing()

    判断是否在开发状态。默认读设置里的 is_dev ，
#### H
编码函数

#### ExitJson
ExitJson($ret)

    打印 json_encode($ret) 并且退出。
    这里的 json 为人眼友好模式。

    实质调用 static::G()->_ExitJson();
#### ExitRedirect
ExitRedirect($url)

    跳转到另一个url 并且退出。
    实质调用 static::G()->_ExitRedirect();
#### ExitRouteTo
ExitRouteTo($url)

    跳转到 URL()函数包裹的 url。
    应用到 static::G()->ExitRedirect(); 和 DNRoute::G()->URL();
    高级开发者注意，这是静态方法里处理的，子类化需要注意
#### Exit404
Exit404()

    404 退出， 实质依次调用 
    高级开发者注意，这是静态方法里处理的，子类化需要注意
#### IsRunning
IsRunning

    判断是否已经开始运行。
    实质调用 DNRuntimeState::G()->isRunning();

#### ThrowOn
ThrowOn($flag,$message,$code=0);

    如果 flag 成立则抛出 DNException 异常。
    减少代码量。如果没这个函数，你要写
    if($flag){throw new DNException($message,$code);}
    如果是你自己的异常类 ，可以 use DNMVCS\DNThrowQuickly 实现 ThrowOn 静态方法。
#### 事件静态方法

OnBeforeShow()

    在输出 view 开始前处理.
    默认处理空模板为当前类和方法，默认关闭数据库。
    因为如果请求时间很长，页面数据量很大。没关闭数据库会导致连接被占用。
OnShow404()

    404 回调。这里没传各种参数，需要的时候从外部获取。
OnException($ex)

    发生未处理异常的处理函数。显示 exception 或 500 页面
OnDevErrorHandler($errno, $errstr, $errfile, $errline)

    处理 Notice ， Decraped 错误。
####  header
    同系统的 header 方法
    注意判断了非 web 状态下不使用
    实际调用 static::G()->_header()
####  exit_system
    代替 exit();
    实际调用 static::G()->exit_sytesm()

    
### 动态函数说明

#### init
init($options=[])

    初始化，这是最经常子类化完成自己功能的方法。
    你可以扩展这个类，添加工程里的其他初始化。
#### run
run()

    开始路由，执行。这个方法拆分出来是为了特定需求, 比如只是为了加载一些类。
    比如 swoole 下不同协程的运行。
    如果404 则返回false;其他返回 true
#### assignPathNamespace
assignPathNamespace($path,$namespace=null)

    分配自动加载的命名空间的目录。
    实质调用 DNAutoLoader::G()->assignPathNamespace();
#### addRouteHook
addRouteHook($hook,$prepend=false,$once=true)

    下钩子扩展 route 方法
    实质调用 DNRoute::G()->addRouteHook
#### getRouteCallingMethod
getRouteCallingMethod()

    获得路由中正在调用的方法。
    用于控制器里判断方法以便于权限管理。
    也适用于重写URL后判断是否是直接访问

    实质调用 DNRoute::G()->getRouteCallingMethod
#### setViewWrapper
setViewWrapper($head_file=null,$foot_file=null)

    给输出 view 加页眉页脚 
    view 里的变量和页眉页脚的域是一样的。
    页眉页脚的变量和 view 页面是同域的。
    有时候你需要 setViewWrapper(null,null) 清理页眉页脚

    实质调用 DNView::G()->setViewWrapper
#### assignViewData
assignViewData($key,$value=null)

    给 view 分配数据，
    这函数用于控制器构造函数添加统一视图数据
    实质调用 DNView::G()->assignViewData
#### assignExceptionHandler
assignExceptionHandler($classes,$callback=null)

    分配特定异常回调。
    用于控制器里控制特定错误类型。
    实质调用 DNExceptionManager::G()->assignExceptionHandler
#### setMultiExceptionHandler
setMultiExceptionHandler(array $classes,$callback)

    多个特定异常回调用于多个异常统一到同一个回调的情况。
    实质调用 DNExceptionManager::G()->setMultiExceptionHandler
#### setDefaultExceptionHandler
setDefaultExceptionHandler($calllback)

    接管默认的异常处理，所有异常都归回调管，而不是显示 500 页面。
    用于控制器里控制特定错误类型。比如 api 调用
    实质调用 DNExceptionManager::G()->setDefaultExceptionHandler

学会这些静态方法，恭喜，基本会用了。
------------------------------------------------------------------------------

我还没学会数据库呢。

这部分是 DNMVCS 的内容

    const DEFAULT_OPTIONS_EX=[
            'use_db'=>true,
            'db_create_handler'=>'',
            'db_close_handler'=>'',
            'db_setting_key'=>'database_list',
            'database_list'=>[],
            
            'rewrite_map'=>[],
            'route_map'=>[],
            
            'ext'=>[],
            'swoole'=>[],
        ];
----

#### RunWithoutPathInfo
#### RunOneFileMode
#### RunAsServer

#### DB
#### DB_W
#### DB_R

RecordsetUrl
RecordsetH

#### setcookie
#### set_exception_handler
#### register_shutdown_function
#### session_start
#### session_destroy
#### session_set_save_handler
#### SG
SG()

    SuperGlobal 的缩写
    返回 DNSuperGlobal 对象
    你可以 DNMVCS::SG()->_GET得到的就是 swoole 也可用的 $_GET 数组。
    类似的还有 _GET,_POST,_REQUEST,_SERVER，_ENV,_COOKIE,_SESSION
#### GLOBALS
static &GLOBALS($k,$v=null)

    兼容 Swoole
    用于替换 global 语法
    也可用 DNMVCS::SG()->GLOBALS;

#### STATICS
static &STATICS($k,$v=null)

    兼容 Swoole
    用于替换 static 语法
#### CLASS_STATICS
static &CLASS_STATICS($class_name,$var_name)

    用于替换类内的 static ，这要提供类名，需要 static::class 或 self::class
----
#### assignRewrite
assignRewrite($old_url,$new_url=null)

    rewrite  重写 path_info
    不区分 request method , 重写后可以用 ? query 参数
    ~ 开始表示是正则 ,为了简单用 / 代替普通正则的 \/
    替换的url ，用 $1 $2 表示参数
#### assignRoute
assignRoute($route,$callback=null)

    给路由加回调。
    关于回调模式的路由。详细情况看之前介绍
    和在 options['route'] 添加数据一样
#### assignStaticMethod

#### assignDynamicMethod
#### extendClassMethodByThirdParty
#### dumpExtMethods

更多配置

## 第三步，使用其他配置

## 第四步，跳转

## 第五步，数据库
## 用 DNMVCS 的起因
最大原因懒，懒得自己写路由。
但是原生的 URL 不够美化,找个能用的路由。
最好是灵活方便修改的(注意这句话)
扩展方便。

## 学习更高级的调用

DNMVCS 扩展了 DNCore,导致 新手容易碰到的问题是: 这函数在  get_class_methods(DNMVCS\DNMVCS::G())里找不到啊，从哪里冒出来的？
调用  DNMVCS::G()->dumpExtMethods(); 就 Dump 出来了。
DNMVCS use trait DNClassExt 一个灵活性就是 DNClassExt

extendClassMethodByThirdParty
dumpExtMethods

todo extendClassMethodByThirdParty htmlattr,css,javascript


-------------------------

## trait DNSingleton | 子类化和 G 方法
**很重要的一节**

```php
<?php
trait DNSingleton
    public static function G($object=null):object
```

G 函数，可变单例模式。

如果没有这个 G 方法 你可能会怎么写代码：
```php
(new MyClass())->foo();
```

绑定 DNSingleton 后，这么写

```php
MyClass::G()->foo();
```

另一个隐藏功能：

```php
MyBaseClass::G(new MyClass())->foo();
```

MyClass 把 MyBaseClass 的 foo 方法替换了。
接下来后面这样的代码，也是调用 MyClass 的 foo2.

```php
MyBaseClass::G()->foo2();
```

**注意:但是静态方法不替换，请注意这一点。**
为什么不是 GetInstance ? 因为太长，这个方法太经常用。

DNMVCS 实现 override_class 的 静态方法，是用 DNClassExt 来实现。

init($options=[],$context=null);
