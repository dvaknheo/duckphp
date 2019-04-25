# DNMVCS 教程
## 快速入门
假定不管什么原因，选用了 DNMVCS 这个框架，需要快速入门.

最快的方式是从 github 下载 DNMVCS。

到所在目录之下运行

```bash
php template/bin/start_server.php
```
浏览器中打开 http://127.0.0.1:8080/ 得到下面欢迎页就表明 OK 了
```text
Don't run the template file directly
Hello DNMVCS

Time Now is [2019-04-19T21:36:06+08:00]
For More Take the DNMVCS-FullTest (TODO)
```
当然，你也可以用 composer 安装

```bash
composer require dnmvcs/framework
```
然后类似的执行命令
```bash
php bin/start_server.php
```
发布的时候，把网站目录指向 public/index.php 就行。

### 第一个任务
路径： http://127.0.0.1:8080/test/done  
作用是显示当前时间的任务。

对照目录结构我们要加个 test/done 显示当前时间

都在各代码段里注释了文件所在相对工程目录的位置

### View 视图
先做出要显示的样子。
```php
<?php // view/test/done.php ?>
<!doctype html><html><body>
<h1>test</h1>
<div><?=$var ?></div>
</body></html>
```
### Controller控制器
写 /test/done 控制器对应的内容
```php
<?php
// app/Controller/test.php
namespace MY\Controller;
use MY\Service\MiscService;
use DNMVCS\DNMVCS as DN;
class test //extends \MY\Base\Controller
{
    public function done()
    {
        $data=[];
        $data['var']=DN::H(MiscService::G()->foo());
        DN::Show($data);
    }
}
```
控制器里，我们处理外部数据，不做业务逻辑，业务逻辑在 Service 层做。

* [DN::H](#DNMVCS::H) 函数 用于编码
* [DN::Show](#DNMVCS::Show) 函数 用于显示视图

### Service 服务
业务逻辑层。
```php
<?php
// app/Service/MiscService.php
namespace MY\Service;
use MY\Model\NoDB_MiscModel;
use DNMVCS\DNSingleton;

class MiscService // extends MY\Base\Service
{
    use DNSingleton;
    public function foo()
    {
        $time=NoDB_MiscModel::G()->getTime();
        $ret="<".$time.">";
        return $ret;
    }
}
```
这里调用了 NoDB_MiscModel 

### Model 模型

完成 NoDB_MiscModel
Model 类是实现基本功能的
这里用 NoDB_ 表示和没使用到数据库

```php
<?php
// app/Model/NoDB_MiscModel.php
namespace MY\Model;
use DNMVCS\DNSingleton;
class NoDB_MiscModel // extends MY\Base\Model
{
    use DNSingleton;
    public function getTime()
    {
        return DATE(DATE_ATOM);
    }
}
```
### 最后显示结果
```text
test

<2019-04-19T22:21:49+08:00>
```

成功！

### 目录结构
接下来，我们回头看工程的目录结构，即默认目录结构

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
+---bin
|       start_server.php    // 启动服务
+---config                  // 配置文件放这里
|       config.php          // 配置，目前是空数组
|       setting.sample.php  // 设置，去除敏感信息的模板
+---headfile                // 头文件处理
|       headfile.php        // 头文件处理
+---view                    // 视图文件放这里，可调
|   |   main.php            // 视图文件
|   \---_sys                // 系统错误视图文件放这里
|           error-404.php   // 404 页面
|           error-500.php   // 500 页面
|           error-debug.php // 调试的时候显示的视图
|           error-exception.php // 异常页面
\---public                  // 网站目录
        index.php           // 主页，入口页
```
文件都不复杂。基本都是空类。
我们主要看的是 入口类。 public/index.php

### DNMVCS 所有方法从浅入深

这一小节将介绍**全部** \DNMVCS\DNMVCS 类 **公开方法**。

我们用 DNMVCS 缩写代替 完整的 DNMVCS\DNMVCS 类。

#### 开始之前
DNMVCS::G(); 默认返回工程中 MY\Base\App 实例。

是否调试状态 DNMVCS::IsDebug()

抛异常：DNMVCS::ThrowOn($flag,$messsage,$code=0); 如果 flag 成立，抛出 Exception;


DNMVCS::DumpExtMethods() 用于查看主程 通过 MY\Base\App 的重载给添加了什么其他方法。

你可能还要去看工程里的 MY\Base\App 里的方法，这些都可以让初始化之后的 DNMVCS 类使用，包括静态方法。

assign 系列函数都是两种调用方式, 单个assign($key,$value) 和 assign($assoc)，后者是批量导入的版本。

#### Controller 编写控制器用到的方法

显示视图用 Core\App::Show($data,$view=null); 如果view 是空等价于 控制器名/方法名 的视图。
最偷懒的是调用 Core\App::Show(get_defined_vars());

如果只显示一块，用 Core\App::ShowBlock($view,$data=null); 如果$data 是空，把父视图的数据带入，
Core\App::ShowBlock 没用到页眉页脚。

在控制器的构造函数中。用 Core\App::G()->setViewWrapper($view_header,$view_footer) 来设置页眉页脚。

Core\App::G()->assignViewData($name,$var) 来预设一些输出。

HTML 编码用 Core\App::H($str); $str 可以是数组。
如果要做权限判断 构造函数里 Core\App::G()->getRouteCallingMethod() 获取当前调用方法。

跳转退出方面。
404 跳转退出 Core\App::Exit404();
302 跳转退出 Core\App::ExitRedirect($url);
302 跳转退出 内部地址 Core\App::ExitRouteTo($url);
输出 Json 退出  Core\App::ExitJson($data);

系统替代函数 
用 Core\App::header() 代替系统 header 兼容命令行等。
用 Core\App::exit_system() 代替系统 exit; 便于接管处理。

用 Core\App::URL($url) 获取相对 url;
用 Core\App::Parameters() 获取切片，对地址重写有效。

异常相关的

如果想接管默认异常，用 Core\App::G()->setDefaultExceptionHandler($handler)

如果对接管特定异常，用 Core\App::G()->assignExceptionHandler($exception_name,$handler)

设置多个异常到回调则用 Core\App::G()->setMultiExceptionHandler($exception_name=[],$handler)

#### Serivce 编写服务用到的方法

获得运行平台 Core\App::Platform()
获得运行平台 Core\App::IsDebug()

获得设置 Core\App::Setting($key) 默认设置文件是  config/setting.php 。
载入配置 Core\App::LoadConfig($key,$basename)
获得配置 Core\App::Config($key)

#### Model 编写模型用到的方法

数据库。

Core\App::DB($tag=null) 获得特定数据库类。
Core\App::DB_R() 获得读数据库类。
Core\App::DB_W() 获得写数据库类。

#### 入口类可能用到其他方法
Core\App::RunQuickly($options,$after_function=null);
Core\App::G()->getOverrideRootClass() 获得重载自类

添加路由和重写  DNMVCS::G()->assignRewrite DNMVCS::G()->assignRoute();

查看则 用 DNMVCS::G()->getRewrites() 和DNMVCS::G()->getRoutes();
自动加载的 DNMVCS::G()->assignPathNamespace ()


动态扩展相关 
扩展静态方法 DNMVCS::G()->assignStaticMethod($method,$callback);
扩展动态方法 DNMVCS::G()->assignDynamicMethod($method,$callback);

DNMVCS::G()->extendClassMethodByThirdParty($class,$static_methods=[],$dyminac_methods=[]);
DNMVCS 调用代理 $class 的方法。

添加路由钩子 Core\App::G()->addRouteHook($hook); $hook 返回空用默认路由处理，否则调用返回的回调。

添加显示前处理 用 Core\App::G()->addBeforeShowHandler($callback)
运行前 Core\App::G()->addBeforeRunHandler($callback)

#### 入口类可能扩充的其他方法

DNMVCS::G()->init($options,$context=null); 初始化

DNMVCS::G()->run();运行

系统替代函数相关 system_wrapper_replace
内部事件方法：

OnBeforeShow 显示之前调用。On404 处理404; OnException  处理异常 OnDevErrorHandler 处理异常模块。

#### 要做 Swoole 兼容。 
Swoole 接口相关 getStaticComponentClasses getDynamicComponentClasses

用 DNMVCS::SG() 代替 超全局变量的 $ 前缀

swoole 兼容session 的替代函数  DNMVCS::session_start DNMVCS::session_destroy session_id() DNMVCS::session_set_save_handler

超全局变量替代函数  DNMVCS::GLOBALS 全局变量 DNMVCS::STATICS 静态变量  DNMVCS::CLASS_STATICS 类静态变量

Swoole 接口相关 DNMVCS::G()->addDynamicClass($class) swoole 协程单例的类。

### 以上

### 从入口开始
```php
<?php
require(__DIR__.'/../headfile/headfile.php');

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



$options
RunQuickly 静态方法
被注释的 init(),run 方法。

### 从入门到精通
我们接下来会逐步学习：


4. 学习 DNMVCS 的配置
4. 调用 DNMVCS 类的静态方法实现目的
5. 调用 DNMVCS 类的动态方法实现目的
6. 学习更高级的调用
7. ---- 核心程序员和高级程序员分界线 ----
8. 扩展 DNMVCS 类
9. 调用扩展类，组件类的动态方法实现目的
10. 继承接管，特定类实现目的
11. 魔改，硬改 DNMVCS 的代码实现目的
这些结构能精简么？
可以，你可以一个目录都不要。

## 核心

### 核心基本选项
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
    'reload_for_flags'=>true,
    
    //// error handler ////
    'error_404'=>'_sys/error-404',
    'error_500'=>'_sys/error-500',
    'error_exception'=>'_sys/error-exception',
    'error_debug'=>'_sys/error-debug',
    
    //// controller ////
        'namespace_controller'=>'Controller',
    'controller_base_class'=>null,
    'controller_prefix_post'=>'do_',
    'controller_hide_boot_class'=>false,
    'controller_welcome_class'=>'Main',
        'controller_enable_paramters'=>false,
        'controller_methtod_for_miss'=>null,
        'controller_index_method'=>'index',
];
```

这是基础的，后面还有一大堆的配置。
总之，这里很明白了。

#### 选项 path
'path'=>null,
基本目录, 其他目录依赖的基础目录，自动处理 /。
#### 选项 namespace
'namespace'=>'MY', 自动处理 \ 。
你的项目的命名空间
#### 选项 path_namespace
'path_namespace'=>'app',
autoload 的命名空间
#### 选项 skip_app_autoload
'skip_app_autoload'=>false,
如果你有其他加载方式，设置为 true;
比如composer.json 里写了  autoload:'app'
#### 选项 override_class
'override_class'=>'Base\App',
**重要选项**

基于 namespace ,如果这个选项的类存在，则 DNCore 在 init 的时候会切换到这个类完成后续初始化，并返回这个类的实例。

注意到 app/Base/App.php 这个文件 MY\Base\App extends DNMVCS\DNMVCS;
如果以  \ 开头则是绝对 命名空间
#### 选项 is_dev
'is_dev'=>false,
配置是否是在开发状态

设置文件的  is_dev 会覆盖
#### 选项 platform
'platform'=>'',

配置开发平台 * 设置文件的  platform 会覆盖

#### 选项 path_view
'path_view'=>'view',
视图的目录，基于 path 配置
#### 选项 path_config
'path_config'=>'config', 配置的目录，基于 path 配置
#### 选项 skip_view_notice_error
'skip_view_notice_error'=>true, view 视图里忽略 notice 错误。
#### 选项 use_inner_error_view
'use_inner_error_view'=>false,  忽略  error_* 配置，使用内部的错误视图

这是个快捷配置的选项。等价于 把 error_* 配置都设置为 null.
#### 选项 setting_file_basename
'setting_file_basename'=>'setting', 如果这项为空，那就不读设置文件了。

这用于不读取设置文件的模式
#### 选项 all_config
'all_config'=>[], 合并入的 config

当你不想读取配置的时候从这里拿 这里的配置会覆盖文件里的。
#### 选项 setting
'setting'=>[], 合并入的 设置

当你不想读取设置的时候从这里拿 这里的设置会覆盖文件里的。
#### 选项 reload_for_flags
'reload_for_flags'=>true,    从设置里重载 is_dev 和 platform
#### 选项 error_404
'error_404'=>'_sys/error-404', 404 页面

error_* 选项为 null 用默认，为 callable 是回调，为string 则是调用视图。
#### 选项 error_500
'error_500'=>'_sys/error-500', 500 页面

error_500 选项 是应对 error,error_exception 选项是应对 exception

error_* 选项为 null 用默认，为 callable 是回调，为string 则是调用视图。
#### 选项 error_exception
'error_exception'=>'_sys/error-exception',

error_500 选项 是应对 error,error_exception 选项是应对 exception

error_* 选项为 null 用默认，为 callable 是回调，为string 则是调用视图。
#### 选项 error_debug
'error_debug'=>'_sys/error-debug',

error_* 选项为 null 用默认，为 callable 是回调，为string 则是调用视图。
#### 选项 namespace_controller
'namespace_controller'=>'Controller',
控制器的命名空间，配合 namespace 选项  
**不建议修改**
#### 选项 controller_base_class
'controller_base_class'=>null,
限定控制器基类，配合 namespace namespace_controller 选项。

如果是 \ 开头的则忽略 namespace namespace_controller 选项。
#### 选项 controller_prefix_post
'controller_prefix_post'=>'do_',
POST 的方法会在方法名前加前缀 do_
不影响  getCallingMethod();

如果想 加其他方法，请执行重载 DNRoute->getMethodToCall()
#### 选项 controller_hide_boot_class
'controller_hide_boot_class'=>false,  不允许欢迎类的其他访问方式。

比如  / 同时可以 用  /Main/index 访问 使用默认 false 是可以的。
#### 选项 controller_enable_paramters
'controller_enable_paramters'=>false, 打开切片模式

不建议打开，会降低性能
**影响重大**
#### 选项 controller_methtod_for_miss
'controller_methtod_for_miss'=>null,

找不到方法名，调用默认方法名。
**不建议修改**
#### 选项 controller_welcome_class
'controller_welcome_class'=>'Main',
默认欢迎类是  Main 。
#### 选项
'controller_index_method'=>'index',
**不建议修改**


## 常见任务

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
[DNMVCS::Setting($key)](#DNMVCS::Setting) 用于读取 config/setting.php 的 $key 。

[DNMVCS::Config($key,$basename='config')](#DNMVCS::Config) 用于读取 config/$basename.php  $key 。

[DNMVCS::LoadConfig($basename='config')](#DNMVCS::LoadConfig)用于载入 config/$basename.php 的内容。

设置是敏感信息。而配置是非敏感

*进阶，更多配置和设置相关 .*
### 常见任务：URL 重写
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
#### 总说
核心静态函数共 24 个。学会这 24个核心静态函数，就基本会 DNMVCS 了。

运行相关的。
入口 快速运行 RunQuickly
运行状态判定 IsRunning
开发状态判定 IsDebug
运行平台 Platform

设置配置。
设置 Setting
配置 Config
载入配置 LoadConfig

显示视图。
显示页面 Show
显示局部块 ShowBlock

地址相关。
获取 URL 
获取 Parameters

跳转退出。
404 跳转 Exit404 
302 跳转 ExitRedirect 
302 跳转 内部 url 退出 ExitRouteTo
输出 Json ExitJson

Html 编码 H();

系统替代函数 
header() 
exit_system()

事件处理 
On404 
OnException 
OnDevErrorHandler
OnBeforeShow

#### DNMVCS::RunQuickly
RunQuickly($options=[],$func_after_init=null)
    
    快速运行
    等价于 DNMVCS::G()->init($options)->run();
    但 $func_after_init 将会在 init 之后运行
#### DNMVCS::IsRunning
IsRunning

    判断是否已经开始运行。
    实质调用 DNRuntimeState::G()->isRunning();
#### DNMVCS::IsDebug
IsDebug()

    判断是否在开发状态。默认读设置里的 is_dev 。
#### DNMVCS::Platform
Platform()

    返回当前环境平台，默认为空默认读设置里的 platform 
#### DNMVCS::Show
Show($data=[],$view=null)

    显示视图
    视图的文件在 ::view 目录底下。你可以通过选项 path_view 调整
    为什么数据放前面，DN::Show(get_defined_vars());把 controller 的变量都整合进来，并用默认路径作为 view 文件。
    实质调用 DNView::G()->_Show();
#### DNMVCS::ShowBlock
ShowBlock($view,$data=null)

    展示一块 view ，用于 View 里嵌套其他 View 或调试的场合。
    展示view不理会页眉页脚，也不做展示的后处理，如关闭数据库。
    注意这里是 $view 在前面， $data 在后面，和 show 函数不一致哦。
    如果 $data===null 那么会继承上级的 view 数据
    实质调用 DNView::G()->_ShowBlock();
#### DNMVCS::URL
URL($url)

    获得调整路由后的url地址 
    当你重写 DNRoute 类后，你可能需要重写这个方法来展示
    比如 simple_route_key 开启后， URL('class/method?foo=bar') 
    将会是 ?r=class/method&foo=bar ，而不是 /class/method?foo=bar
    如果是 / 开始的 URL ，将是从网站根目录开始。

    实质调用 DNRoute::G()->_URL();
#### DNMVCS::Parameters
Parameters()

    获得路径切片
    当用正则匹配路由的时候，匹配结果放在这里。
    如果开启了 eanbale_parameter 匹配选项也会在这里。
    这会使得 /about/foo/123/456 路由调用方法为 => about->foo(123,456);

    实质调用 DNRoute::G()->_Parameters();

#### DNMVCS::Setting
Setting($key)

    读取设置
    设置在 ::/config/setting.php 里，php 格式
    设置是敏感信息，不保存在版本管理中。
    配置非敏感信息，放在版本管理中.
    实质调用 DNConfig::G()->_Setting();
#### DNMVCS::Config
Config($key,$file_basename='config')

    读取配置 
    配置放在 config/$file_basename.php 里，php 格式
    配置是放在非敏感信息，放在版本管理中
    实质调用 DNConfig::G()->_Config();
#### DNMVCS::LoadConfig
LoadConfig($file_basename)

    加载其他配置
    如果很多配置文件，手动加载其他配置
    实质调用 DNConfig::G()->LoadConfig();

#### DNMVCS::H
编码函数
#### DNMVCS::ExitJson
ExitJson($ret)

    打印 json_encode($ret) 并且退出。
    这里的 json 为人眼友好模式。

    实质调用 static::G()->_ExitJson();
#### DNMVCS::ExitRedirect
ExitRedirect($url)

    跳转到另一个url 并且退出。
    实质调用 static::G()->_ExitRedirect();
#### DNMVCS::ExitRouteTo
ExitRouteTo($url)

    跳转到 URL()函数包裹的 url。
    应用到 static::G()->ExitRedirect(); 和 DNRoute::G()->URL();
    高级开发者注意，这是静态方法里处理的，子类化需要注意
#### DNMVCS::Exit404
Exit404()

    404 退出， 实质依次调用 static::On404, static::exit_system();
    高级开发者注意，这是静态方法里处理的，子类化需要注意


#### DNMVCS::ThrowOn
ThrowOn($flag,$message,$code=0);

    如果 flag 成立则抛出 DNException 异常。
    减少代码量。如果没这个函数，你要写
    if($flag){throw new DNException($message,$code);}
    如果是你自己的异常类 ，可以 use DNMVCS\DNThrowQuickly 实现 ThrowOn 静态方法。
#### DNMVCS::OnBeforeShow
OnBeforeShow()

    在输出 view 开始前处理.
    默认处理空模板为当前类和方法，默认关闭数据库。
    因为如果请求时间很长，页面数据量很大。没关闭数据库会导致连接被占用。
#### DNMVCS::On404
OnShow404()

    404 回调。这里没传各种参数，需要的时候从外部获取。
#### DNMVCS::OnException
OnException($ex)

    发生未处理异常的处理函数。显示 exception 或 500 页面
#### DNMVCS::OnDevErrorHandler
OnDevErrorHandler($errno, $errstr, $errfile, $errline)

    处理 Notice ， Decraped 错误。
#### DNMVCS::header
header()
    同系统的 header 方法
    注意判断了非 web 状态下不使用
    实际调用 static::G()->_header()
#### DNMVCS::exit_system
exit()
    代替 exit();
    实际调用 static::G()->exit_sytesm()

### 动态函数参考
12 个动态函数，一般你不怎么用到的 getRouteCallingMethod 这个会常用点

运行的init run

视图的 setViewWrapper assignViewData

路由的 addRouteHook getRouteCallingMethod

异常相关的 assignExceptionHandler setMultiExceptionHandler setDefaultExceptionHandler

自动加载的 assignPathNamespace

系统相关的 addBeforeShowHandler getOverrideRootClass
#### DNMVCS->init
init($options=[])

    初始化，这是最经常子类化完成自己功能的方法。
    你可以扩展这个类，添加工程里的其他初始化。
#### DNMVCS->run
run()

    开始路由，执行。这个方法拆分出来是为了特定需求, 比如只是为了加载一些类。
    比如 swoole 下不同协程的运行。
    如果404 则返回false;其他返回 true
#### DNMVCS->setViewWrapper
setViewWrapper($head_file=null,$foot_file=null)

    给输出 view 加页眉页脚 
    view 里的变量和页眉页脚的域是一样的。
    页眉页脚的变量和 view 页面是同域的。
    有时候你需要 setViewWrapper(null,null) 清理页眉页脚

    实质调用 DNView::G()->setViewWrapper
#### DNMVCS->assignViewData
assignViewData($key,$value=null)

    给 view 分配数据，
    这函数用于控制器构造函数添加统一视图数据
    实质调用 DNView::G()->assignViewData
#### DNMVCS->assignPathNamespace
assignPathNamespace($path,$namespace=null)

    分配自动加载的命名空间的目录。
    实质调用 DNAutoLoader::G()->assignPathNamespace();
#### DNMVCS->addRouteHook
addRouteHook($hook,$prepend=false,$once=true)

    下钩子扩展 route 方法
    实质调用 DNRoute::G()->addRouteHook
#### DNMVCS->getRouteCallingMethod
getRouteCallingMethod()

    获得路由中正在调用的方法。
    用于控制器里判断方法以便于权限管理。
    也适用于重写URL后判断是否是直接访问

    实质调用 DNRoute::G()->getRouteCallingMethod

#### DNMVCS->assignExceptionHandler
assignExceptionHandler($classes,$callback=null)

    分配特定异常回调。
    用于控制器里控制特定错误类型。
    实质调用 DNExceptionManager::G()->assignExceptionHandler
#### DNMVCS->setMultiExceptionHandler
setMultiExceptionHandler(array $classes,$callback)

    多个特定异常回调用于多个异常统一到同一个回调的情况。
    实质调用 DNExceptionManager::G()->setMultiExceptionHandler
#### DNMVCS->setDefaultExceptionHandler
setDefaultExceptionHandler($calllback)

    接管默认的异常处理，所有异常都归回调管，而不是显示 500 页面。
    用于控制器里控制特定错误类型。比如 api 调用
    实质调用 DNExceptionManager::G()->setDefaultExceptionHandler

学会这些静态方法，恭喜，基本会用了。
### 总结
这部分其实是 DNCore 这个类的内容，
如果你不需要下面的扩展，用 DNCore 就够了。
接下来是 DNMVCS 这个类的扩展

------------------------------------------------------------------------------
## 高级
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
### DNMVCS 扩展的静态方法
#### 总说
DNMVCS 相比 DNCore 扩展了一些方法按类型大致分为

更多的运行方式  RunWithoutPathInfo RunOneFileMode RunAsServer
为什么没 Redis ?

为了 swoole 兼容，更多的系统代替函数 setcookie set_exception_handler register_shutdown_function
swoole 兼容session  替代函数  session_start session_destroy session_set_save_handler
超全局变量替代函数  SG GLOBALS STATICS CLASS_STATICS

#### RunWithoutPathInfo
#### RunOneFileMode
#### RunAsServer

#### DB
#### DB_W
#### DB_R

#### RecordsetUrl
#### RecordsetH

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
&GLOBALS($k,$v=null)

    兼容 Swoole
    用于替换 global 语法
    也可用 DNMVCS::SG()->GLOBALS;

#### STATICS
&STATICS($k,$v=null)

    兼容 Swoole
    用于替换 static 语法
#### CLASS_STATICS
&CLASS_STATICS($class_name,$var_name)

    用于替换类内的 static ，这要提供类名，需要 static::class 或 self::class
----



### DNMVCS 扩展的动态函数

添加路由和重写  assignRewrite assignRoute
Swoole 接口相关的3个函数  addDynamicClass getBootInstances getDynamicClasses
动态扩展相关 assignStaticMethod assignDynamicMethod extendClassMethodByThirdParty
系统替代函数相关 system_wrapper_replace

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

更多配置

## 用 DNMVCS 的起因

## 学习更高级的调用

DNMVCS 扩展了 DNCore,导致 新手容易碰到的问题是: 这函数在  get_class_methods(DNMVCS\DNMVCS::G())里找不到啊，从哪里冒出来的？
调用  DNMVCS::DumpExtMethods(); 就 Dump 出来了。
DNMVCS use trait DNClassExt 一个灵活性就是 DNClassExt

extendClassMethodByThirdParty


todo  htmlattr,css,javascript


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
为什么不是 GetInstance ? 因为太长，这个方法太经常用。
**注意:但是静态方法不替换，请注意这一点。**
DNMVCS 实现 override_class 的 静态方法，是用 DNClassExt 来实现。


## 索引

### 静态函数

* [DNMVCS::CLASS_STATICS](#DNMVCS::CLASS_STATICS)
* [DNMVCS::Config](#DNMVCS::Config)
* [DNMVCS::DB](#DNMVCS::DB)
* [DNMVCS::DB_R](#DNMVCS::DB_R)
* [DNMVCS::DB_W](#DNMVCS::DB_W)
* [DNMVCS::IsDebug](#DNMVCS::IsDebug)
* [DNMVCS::DumpExtMethods](#DNMVCS::DumpExtMethods)
* [DNMVCS::Exit404](#DNMVCS::Exit404)
* [DNMVCS::ExitJson](#DNMVCS::ExitJson)
* [DNMVCS::ExitRedirect](#DNMVCS::ExitRedirect)
* [DNMVCS::ExitRouteTo](#DNMVCS::ExitRouteTo)
* [DNMVCS::G](#DNMVCS::G)
* [DNMVCS::GLOBALS](#DNMVCS::GLOBALS)
* [DNMVCS::H](#DNMVCS::H)
* [DNMVCS::Import](#DNMVCS::Import)
* [DNMVCS::IsRunning](#DNMVCS::IsRunning)
* [DNMVCS::LoadConfig](#DNMVCS::LoadConfig)
* [DNMVCS::On404](#DNMVCS::On404)
* [DNMVCS::OnBeforeShow](#DNMVCS::OnBeforeShow)
* [DNMVCS::OnDevErrorHandler](#DNMVCS::OnDevErrorHandler)
* [DNMVCS::OnException](#DNMVCS::OnException)
* [DNMVCS::Parameters](#DNMVCS::Parameters)
* [DNMVCS::Platform](#DNMVCS::Platform)
* [DNMVCS::RecordsetH](#DNMVCS::RecordsetH)
* [DNMVCS::RecordsetUrl](#DNMVCS::RecordsetUrl)
* [DNMVCS::RunAsServer](#DNMVCS::RunAsServer)
* [DNMVCS::RunOneFileMode](#DNMVCS::RunOneFileMode)
* [DNMVCS::RunQuickly](#DNMVCS::RunQuickly)
* [DNMVCS::RunWithoutPathInfo](#DNMVCS::RunWithoutPathInfo)
* [DNMVCS::SG](#DNMVCS::SG)
* [DNMVCS::STATICS](#DNMVCS::STATICS)
* [DNMVCS::Setting](#DNMVCS::Setting)
* [DNMVCS::Show](#DNMVCS::Show)
* [DNMVCS::ShowBlock](#DNMVCS::ShowBlock)
* [DNMVCS::ThrowOn](#DNMVCS::ThrowOn)
* [DNMVCS::URL](#DNMVCS::URL)
* [DNMVCS::exit_system](#DNMVCS::exit_system)
* [DNMVCS::header](#DNMVCS::header)
* [DNMVCS::register_shutdown_function](#DNMVCS::register_shutdown_function)
* [DNMVCS::session_destroy](#DNMVCS::session_destroy)
* [DNMVCS::session_set_save_handler](#DNMVCS::session_set_save_handler)
* [DNMVCS::session_start](#DNMVCS::session_start)
* [DNMVCS::set_exception_handler](#DNMVCS::set_exception_handler)
* [DNMVCS::setcookie](#DNMVCS::setcookie)
* [DNMVCS::system_wrapper_get_providers](#DNMVCS::system_wrapper_get_providers)
### 动态函数
* [DNMVCS->addBeforeShowHandler](#DNMVCS->addBeforeShowHandler)
* [DNMVCS->addDynamicClass](#DNMVCS->addDynamicClass)
* [DNMVCS->addRouteHook](#DNMVCS->addRouteHook)
* [DNMVCS->assignDynamicMethod](#DNMVCS->assignDynamicMethod)
* [DNMVCS->assignExceptionHandler](#DNMVCS->assignExceptionHandler)
* [DNMVCS->assignPathNamespace](#DNMVCS->assignPathNamespace)
* [DNMVCS->assignRewrite](#DNMVCS->assignRewrite)
* [DNMVCS->assignRoute](#DNMVCS->assignRoute)
* [DNMVCS->assignStaticMethod](#DNMVCS->assignStaticMethod)
* [DNMVCS->assignViewData](#DNMVCS->assignViewData)
* [DNMVCS->checkDBPermission](#DNMVCS->checkDBPermission)
* [DNMVCS->extendClassMethodByThirdParty](#DNMVCS->extendClassMethodByThirdParty)
* [DNMVCS->getBootInstances](#DNMVCS->getBootInstances)
* [DNMVCS->getDynamicClasses](#DNMVCS->getDynamicClasses)
* [DNMVCS->getOverrideRootClass](#DNMVCS->getOverrideRootClass)
* [DNMVCS->getRouteCallingMethod](#DNMVCS->getRouteCallingMethod)
* [DNMVCS->init](#DNMVCS->init)
* [DNMVCS->run](#DNMVCS->run)
* [DNMVCS->setDefaultExceptionHandler](#DNMVCS->setDefaultExceptionHandler)
* [DNMVCS->setMultiExceptionHandler](#DNMVCS->setMultiExceptionHandler)
* [DNMVCS->setViewWrapper](#DNMVCS->setViewWrapper)
* [DNMVCS->system_wrapper_replace](#DNMVCS->system_wrapper_replace)
