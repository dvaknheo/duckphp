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
use MY\Base\BaseController;
use MY\Base\ControllerHelper as C;

use MY\Service\MiscService;
class test //extends BaseController
{
    public function done()
    {
        $data=[];
        $data['var']=C::H(MiscService::G()->foo());
        C::Show($data);
    }
}
```
控制器里，我们处理外部数据，不做业务逻辑，业务逻辑在 Service 层做。


### Service 服务
业务逻辑层。
```php
<?php
// app/Service/MiscService.php
namespace MY\Service;
use MY\Base\BaseService;
use MY\Base\ServiceHelper as S;
use MY\Model\NoDB_MiscModel;

class MiscService extends BaseService
{
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

use MY\Base\BaseModel;
use MY\Base\ModelHelper as M;

class NoDB_MiscModel extends BaseModel
{
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
### 接下来 Controller, Service, Model 该怎么写
#### 简化架构图
同级之间的东西不能相互调用
#### 完整架构图

#### 开始之前

命名空间 MY 是 可调的。比如调整成 MyProject ,TheBigOneProject  等

作为应用程序员， 你不能引入 DNMVCS 的任何东西，就当 DNMVCS 命名空间不存在。
主力程序员才去研究 DNMVCS 类的东西。

写 Model 你可能要引入 MY\Base\ModelHelper 类 记为  。
写 Serivce 你可能要引入 MY\Base\SerivceHelper 类。
写 Controller 你可能要引入 MY\Base\ControllerHelper 类。

不能交叉引入其他 Helper 类。如果需要交叉，那么你就是错的。


G 函数， G() 函数就是可变单例函数，当你用到助手类的 G 函数的形式的时候，说明你这个功能不常用。

assign* 系列函数都是两种调用方式, 单个assign($key,$value) 和 assign($assoc)，后者是批量导入的版本。


#### Model 编写模型用到的方法

抛异常：M::ThrowOn($flag,$messsage,$code=0,$exception_class=null); 如果 flag 成立，抛出 $exception_class(默认为 Exception 类);

M::ThrowOn();

Model 类 还引用到相关

M::DB($tag=null) 获得特定数据库类。
M::DB_R() 获得读数据库类。
M::DB_W() 获得写数据库类。

#### Serivce 编写服务用到的方法

和 ModelHelper 一样也有同功能的 
S::ThrowOn();

此外还有

* 判断是否在调试状态 S::IsDebug();
* 判断所在平台 S::Platform();

* 获得设置 S::Setting($key) 默认设置文件是在  config/setting.php 。
* 载入配置 S::LoadConfig($key,$basename)
* 获得配置 S::Config($key)

设置是敏感信息,不存在于版本控制里面。而配置是非敏感

#### Controller 编写控制器用到的方法

和 Service 的同名同作用方法

* C::IsDebug();
* C::Platform();
* C::Setting($key);
* C::Config($key);
* C::LoadConfig($key,$basename)

除去  Service 的同名方法 外，还有

1. 显示相关的

    显示视图用 C::Show($data,$view=null); 如果view 是空等价于 控制器名/方法名 的视图。
    最偷懒的是调用 C::Show(get_defined_vars());

    如果只显示一块，用 C::ShowBlock($view,$data=null); 如果$data 是空，把父视图的数据带入，
    

    在控制器的构造函数中。用 C::setViewWrapper($view_header,$view_footer) 来设置页眉页脚。
    页眉页脚的变量和 view 页面是同域的。 setViewWrapper(null,null) 清理页眉页脚。
    C::ShowBlock 没用到页眉页脚。而且 C::ShwoBlock 只单纯输出，不做多余动作。

    C::assignViewData($name,$var) 来设置视图的输出。

    HTML 编码用 C::H($str); $str 可以是数组。
2. 跳转退出方面

    404 跳转退出 C::Exit404();
    302 跳转退出 C::ExitRedirect($url);
    302 跳转退出 内部地址 C::ExitRouteTo($url);
    输出 Json 退出  C::ExitJson($data);

3. 路由相关

    用 C::URL($url) 获取相对 url;

    用 C::Parameters() 获取切片，对地址重写有效。
    如果要做权限判断 构造函数里 C::getRouteCallingMethod() 获取当前调用方法。

    用 C::getRewrites() 和 C::getRoutes(); 查看 rewrite 表，和 路由表。

4. 系统替代函数 
    用 C::header() 代替系统 header 兼容命令行等。
    用 C::setcookie() 代替系统 setcookie 兼容命令行等。
    用 C::exit_system() 代替系统 exit; 便于接管处理。

setcookie

5. 异常相关

    如果想接管默认异常，用 C::G()->setDefaultExceptionHandler($handler);
    如果对接管特定异常，用 C::G()->assignExceptionHandler($exception_name,$handler);
    设置多个异常到回调则用 C::G()->setMultiExceptionHandler($exception_name=[],$handler);

6. Swoole 兼容

    如果想让你们的项目在 swoole 下也能运行，那就要加上这几点
    用 C::SG() 代替 超全局变量的 $ 前缀 如 $_GET =>  App::SG->_GET

    使用以下参数格式都一样的 swoole 兼容静态方法，代替同名全局方法。

    C::session_start(),
    C::session_destroy(),
    C::session_id()，
    如 session_start() => C::session_start();。

    或许你会用到 C::RecordsetUrl(),C::RecordsetH()

#### 编写 兼容 Swoole 的代码

全局变量 global $a='val'; =>  $a=C::GLOBALS('a','val');

静态变量 global $a='val'; =>  $a=C::STATICS('a','val');

类内静态变量
$x=static::$abc; => $x=C::CLASS_STATICS(static::class,'abc');

#### 学习高级路由


assignRewrite($old_url,$new_url=null)

    rewrite  重写 path_info
    不区分 request method , 重写后可以用 ? query 参数
    ~ 开始表示是正则 ,为了简单用 / 代替普通正则的 \/
    替换的url ，用 $1 $2 表示参数
assignRoute($route,$callback=null)

    给路由加回调。
    关于回调模式的路由。详细情况看之前介绍
    和在 options['route'] 添加数据一样

#### 高级程序员

如果你想偷懒， *Helper 改为 App 由 MY\Base\App 自己的实现也行。

如果要完成 rewrite ，或者自定义路由。那就得调整 MY\Base\App 类了。

在 init(), run() 方法里你会用到
addRouteHook($hook,$prepend=false,$once=true)

添加路由和重写  $this->assignRewrite $this->assignRoute();
添加路由钩子 $this->addRouteHook($hook); $hook 返回空用默认路由处理，否则调用返回的回调。
$this->stopRunDefaultRouteHook($hook)


$this->extendClassMethodByThirdParty($class,$static_methods=[],$dyminac_methods=[]);
DNMVCS 调用代理 $class 的方法。


添加显示前处理 用 $this->addBeforeShowHandler($callback)
运行前 $this->addBeforeRunHandler($callback)

动态扩展相关 

扩展静态方法 $this->assignStaticMethod($method,$callback);
扩展动态方法 $this->assignDynamicMethod($method,$callback);

自动加载的 $this->assignPathNamespace()

### 目录结构
在看默认选项前
我们看工程的目录结构，即默认目录结构

```text
+---app                     // psr-4 标准的自动加载目录
|   +---Base                // 基类放在这里
|   |      App.php          // 默认框架入口文件
|   |      BaseContrllor.php    // 控制器基类
|   |      BaseModel.php        // 模型基类
|   |      BaseService.php      // 服务基类
|   |      ContrllorHelper.php  // 控制器助手类
|   |      ModelHelper.php      // 模型助手类
|   |      ServiceHelper.php    // 服务助手类
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
文件都不复杂。基本都是空类或空继承类，便于不同处理。
这些结构能精简么？
可以，你可以一个目录都不要。


### 从入口开始
我们主要看的是 入口类。 public/index.php

```php
<?php
require(__DIR__.'/../headfile/headfile.php');

$path=realpath(__DIR__.'/..');
$options=[
    'path'=>$path,
    'namespace'=>'MY',
];
if (defined('DNMVCS_WARNING_IN_TEMPLATE')) {
    $options['skip_setting_file']=true;
    $options['is_dev']=true;
    echo "<div>Don't run the template file directly </div>\n";
}
\DNMVCS\DNMVCS::RunQuickly($options);
// \DNMVCS\DNMVCS::G()->init($options)->run();
```



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

    'skip_view_notice_error'=>true,
    'use_inner_error_view'=>false,
    
    //// config ////
    'path_config'=>'config',
    'all_config'=>[],
    'setting'=>[],
    'setting_file'=>'setting',
    'skip_setting_file'=>false,
    'reload_for_flags'=>true,
    
    //// error handler ////
    'error_404'=>'_sys/error-404',
    'error_500'=>'_sys/error-500',
    'error_exception'=>'_sys/error-exception',
    'error_debug'=>'_sys/error-debug',
    
    //// controller ////
    // 'namespace_controller'=>'Controller',
    'controller_base_class'=>null,
    'controller_prefix_post'=>'do_',
    'controller_hide_boot_class'=>false,
    'controller_welcome_class'=>'Main',
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

基于 namespace ,如果这个选项的类存在，则在 init 的时候会切换到这个类完成后续初始化，并返回这个类的实例。

注意到 app/Base/App.php 这个文件的类 MY\Base\App extends DNMVCS\DNMVCS;
如果以  \ 开头则是绝对 命名空间
#### 选项 is_dev
'is_debug'=>false,
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
这是个快捷配置的选项。等价于 把 error_* 配置都设置为 null. 用于调试
####  选项 use_404_to_other_framework
'use_404_to_other_framework'=>false,  
这是个快捷配置的选项。用于 404 后整合其他框架 等价于 error_404=function(){}

#### 选项 skip_setting_file
'skip_setting_file'=>false, 如果这项为空，那就不读设置文件了。

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


找不到方法名，调用默认方法名。
**不建议修改**
#### 选项 controller_welcome_class
'controller_welcome_class'=>'Main',
默认欢迎类是  Main 。

## 常见任务

OK 安装好了，用了 路由， URL 也要更改，所以我们要调用 DN::URL 来显示路由。

*进阶，更多配置和设置相关 .*
### 常见任务：URL 重写
$options['rewrite_map'] 用于重写 url . 以 ~ 开始的表示正则，同时省略 / 必须转义的。 用 $ 代替 \ 捕获。
$options['route_map'] ,用于 回调式路由， 除了  :: 表示类的静态方法，还 -> 符号表示的是类的动态方法。
key  可以加 GET POST 方法。


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

原理是由其他框架去处理 404。
其他框架整合 DNMVCS ,则在相应处理 404 的地方开始



#### DNMVCS::RunQuickly
RunQuickly($options=[],$func_after_init=null)
    
    快速运行
    等价于 DNMVCS::G()->init($options)->run();
    但 $func_after_init 将会在 init 之后运行
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


更多的运行方式  RunWithoutPathInfo RunOneFileMode RunAsServer
为什么没 Redis ?


####  补充那些没提到的选项
全选项里还有。


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