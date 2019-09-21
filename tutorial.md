# DNMVCS 教程
## 第一章 快速入门
### 安装
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
发布的时候，把网站目录指向 public/index.php 就行。
### 另一种安装模式： Composer 安装
在工程目录下执行：
```
composer require dnmvcs/framework
./vendor/bin/dnmvcs --create
```
将会直接把 template 的东西复制到工程并做调整，同样执行
```bash
php bin/start_server.php
```
浏览器中打开 http://127.0.0.1:8080/ 得到下面欢迎页就表明 OK 了
细则可以看 --help 参数

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

// use MY\Base\BaseController;
use MY\Base\Helper\ControllerHelper as C;
use MY\Service\MiscService;

class test // extends BaseController
{
    public function done()
    {
        $data=[];
        $data['var']=C::H(MiscService::G()->foo());
        C::Show($data); // C::Show($data,'test/done');
    }
}
```
控制器里，我们处理外部数据，不做业务逻辑，业务逻辑在 Service 层做。

BaseController  这个基类，如果不强制要求也可以不用。

MY 这个命名空间前缀可在选项 ['namespace'] 中变更。

C::H 用来做 html编码。

C::Show($data); 是 C::Show($data,'test/done'); 的缩写， 调用 test/done 这个视图。

### Service 服务
业务逻辑层。
```php
<?php
// app/Service/MiscService.php
namespace MY\Service;

use MY\Base\Helper\ServiceHelper as S;
use MY\Base\BaseService;
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
BaseService 也是不强求的，我们 extends BaseService 是为了能用 G 函数这个单例方法

这里调用了 NoDB_MiscModel 

### Model 模型

完成 NoDB_MiscModel 。

Model 类是实现基本功能的。

这里用 NoDB_ 前缀表示我们没使用到数据库。

```php
<?php
// app/Model/NoDB_MiscModel.php
namespace MY\Model;

use MY\Base\BaseModel;
use MY\Base\Helper\ModelHelper as M;

class NoDB_MiscModel extends BaseModel
{
    public function getTime()
    {
        return DATE(DATE_ATOM);
    }
}
```
同样 BaseModel 也是不强求的，我们 extends BaseModel 是为了能用 G 函数这个单例方法

### 最后显示结果
```text
test

<2019-04-19T22:21:49+08:00>
```

## 第二章 DNMVCS 应用普通开发人员参考

### 基本的 DMMVCS 目录结构

简化版本的新建工程的桩代码。和描述。
```text
+---app                     // psr-4 标准的自动加载目录
|   +---Controller          // 控制器目录
|   |       Main.php        // 默认控制器
|   +---Model               // 模型目录
|   |       TestModel.php   // 测试模型
|   \---Service             // 服务目录
|           TestService.php // 测试 Service
+---config                  // 配置文件放这里
|       config.php          // 配置，目前是空数组
|       setting.sample.php  // 设置，去除敏感信息的模板
+---view                    // 视图目录
|   |   main.php            // 视图文件
|   \---_sys                // 系统错误视图文件放这里
|           error-404.php   // 404 页面
|           error-500.php   // 500 页面
|           error-debug.php // 调试的时候显示的视图
|           error-exception.php // 异常页面
\---public                  // 网站目录
        index.php           // 主页，入口页
```
这是简化版本的工程文件架构图，为了对应用普通开发人员。省掉了一些文件。
完整的工程文件架构图在后面章节。

### 应用工程完整架构图
你的工程应该是这么架构
![arch_full](doc/arch_full.gv.svg)
同级之间的东西不能相互调用

### 开始编码之前

命名空间 MY 是 可调的。比如调整成 MyProject ,TheBigOneProject  等。
参见 $options['namespace']

作为应用程序员， 你不能引入 DNMVCS 的任何东西，就当 DNMVCS 命名空间不存在。
核心程序员才去研究 DNMVCS 类的东西。

* 写 Model 你可能要引入 MY\Base\Helper\ModelHelper 助手类别名为 M 。
* 写 Serivce 你可能要引入 MY\Base\Helper\SerivceHelper 助手类别名为 S 。
* 写 Controller 你可能要引入 MY\Base\Helper\ControllerHelper 助手类别名为 C 。
* 写 View 你可能要引入 MY\Base\Helper\ViewHelper 助手类别名为 V 。
* 不能交叉引入其他层级的助手类。如果需要交叉，那么你就是错的。
* 小工程可以用直接使用入口类 MY\Base\App 类，这包含了上述类的公用方法。

### 助手类的公用静态方法

ThrowOn($flag,$messsage,$code=0,$exception_class=null)
    
    抛异常 如果 flag 成立，抛出 $exception_class(默认为 \Exception 类);
GetExtendStaticStaticMethodList()

    用来查看当前类有什么额外的静态方法。
IsDebug()

    判断是否在调试状态
Platform()

    判断所在平台;
Setting($key);

    获得设置，默认设置文件是在  config/setting.php 。
    设置是敏感信息,不存在于版本控制里面。而配置是非敏感。
LoadConfig($key,$basename="config");

    载入配置，Config($key); 获得配置 默认配置文件是在  config/config.php 。

### View 编写视图用到的方法
V::ShowBlock($view, $data)

V::H($str)
V::DumpTrace()
V::Dump()

### Model 编写模型用到的方法
Model 类。数据库相关

* M::DB($tag=null) 获得特定数据库类。
* M::DB_R() 获得读数据库类。
* M::DB_W() 获得写数据库类。

数据库如何使用？ 参见后面章节。

### Serivce 编写服务用到的方法

ServiceHelper 默认没有额外方法，看你的核心开发人员是否加上。

### Controller 编写控制器用到的方法

ControllerHelper 类的方法比较多，大致学完就全部会用了。

##### 1. 显示相关的

    C::Show($data,$view=null);

    显示视图用  如果view 是空等价于 控制器名/方法名 的视图。
    最偷懒的是调用 C::Show(get_defined_vars());

    C::ShowBlock($view,$data=null);
    
    如果只显示一块，用  如果$data 是空，把父视图的数据带入，
    
    C::setViewWrapper($view_header,$view_footer)
    在控制器的构造函数中。用  来设置页眉页脚。
    
    页眉页脚的变量和 view 页面是同域的。 用 C::setViewWrapper(null,null) 清理页眉页脚。
    另 C::ShowBlock 没用到页眉页脚。而且 C::ShwoBlock 只单纯输出，不做多余动作。

    C::assignViewData($name,$var); 设置视图的输出。

    C::H($str); HTML 编码用  $str 可以是数组。

##### 2. 跳转退出方面

    404 跳转退出 C::Exit404();
    302 跳转退出 C::ExitRedirect($url);
    302 跳转退出内部地址 C::ExitRouteTo($url);
    输出 Json 退出  C::ExitJson($data);
##### 3. 路由相关

    C::URL($url) 获取相对 url;
    C::getRouteCallingMethod() 获取当前调用方法。常用于构造函数里做权限判断。
    C::setRouteCallingMethod($method) 设置当前调用方法，不常用，用于跨方法调用场合。

##### 4. 系统替代函数 

    用 C::header() 代替系统 header 兼容命令行等。
    用 C::setcookie() 代替系统 setcookie 兼容命令行等。
    用 C::exit_system() 代替系统 exit; 便于接管处理。 
    用 C::set_exception_handler() 代替系统 set_exception_handler 便于接管处理。
    用 C::register_shutdown_function() 代替系统 set_exception_handler 便于接管处理。
##### 5. 异常相关

    如果想接管默认异常，用 C::setDefaultExceptionHandler($handler);
    如果对接管特定异常，用 C::assignExceptionHandler($exception_name,$handler);
    设置多个异常到回调则用，C::setMultiExceptionHandler($exception_name=[],$handler);
##### 6.兼容 Swoole

    如果想让你们的项目在 swoole 下也能运行，那就要加上这几点
    用 C::SG() 代替 超全局变量的 $ 前缀 如 $_GET =>  C::SG()->_GET

    使用以下参数格式都一样的 swoole 兼容静态方法，代替同名全局方法。

    C::session_start(),
    C::session_destroy(),
    C::session_id()，
    如 session_start() => C::session_start();

    编写 Swoole 相容的代码，还需要注意到一些写法的改动。
全局变量
```php
global $a='val'; =>  $a=C::GLOBALS('a','val');
```php
静态变量 
```php
static $a='val'; =>  $a=C::STATICS('a','val');
```
类内静态变量
```php
$x=static::$abc; => $x=C::CLASS_STATICS(static::class,'abc');
```
##### 7.高级路由

用 C::Parameters() 获取切片，对地址重写有效。
如果要做权限判断 构造函数里 C::getRouteCallingMethod() 获取当前调用方法。

用 C::getRewrites() 和 C::getRoutes(); 查看 rewrite 表，和 路由表。

assignRewrite($old_url,$new_url=null)

    支持单个 assign($key,$value) 和多个 assign($assoc)

    rewrite  重写 path_info
    不区分 request method , 重写后可以用 ? query 参数
    ~ 开始表示是正则 ,为了简单用 / 代替普通正则的 \/
    替换的url ，用 $1 $2 表示参数
assignRoute($route,$callback=null)

    给路由加回调。
    单个 assign($key,$value) 和多个 assign($assoc)；
    关于回调模式的路由。详细情况看之前介绍
    和在 options['route'] 添加数据一样

    或许你会用到 C::RecordsetUrl(),C::RecordsetH()

高级方法 C::MapToService($serviceClass, $input) 

    映射当前方法 到相应的 service 类 $input 为 GET 或 POST

高级方法 explodeService($controller_object, $namespace="MY\\Service\\")

    你们想要的 $this->load 。 把 Service 后缀的改过来。 自动加载
    如 MY\Service\TestService::G()->foo(); => $this->testService->foo();
    暂时不建议使用。
### 其他要点
配置和设置在哪里？
TestService::G()->foo 里的 G() 是什么意。G函数，单例模式。

JsonExt\MY\Service\TestService 这个类找不到啊。
这是第三方的扩展
## 第三章 DNMVCS 应用核心开发人员参考
### 入口文件
我们看入口类文件 public/index.php

```php
<?php
require(__DIR__.'/../vendor/autoload.php');

$path=realpath(__DIR__.'/..');
$options=[];

$options['path']=$path;
$options['namespace']='MY';
\DNMVCS\DNMVCS::RunQuickly($options, function () {
});
// \DNMVCS\DNMVCS::G()->init($options)->run();
// var_export(\DNMVCS\DNMVCS::G()->options);
```
入口类前面部分是处理头文件的。
然后处理直接 copy 代码提示，不要直接运行。
起作用的主要就这句话
```php
\DNMVCS\DNMVCS::RunQuickly($options, function () {
});
```
相当于后面调用的 \DNMVCS\DNMVCS::G()->init($options)->run();

init, run 分两步走的模式。

最后留了 dump 选项的语句。

接下来我们看 $options 里可以选什么

### 核心基本选项
```php
const DEFAULT_OPTIONS=[
    //// basic ////
    'path'=>null,               // 基本目录, 其他目录依赖的基础目录，自动处理 “/”。
    'namespace'=>'MY',          // 工程的 autoload 的命名空间
    'path_namespace'=>'app',    // 工程对应的命名空间 目录
    
    'skip_app_autoload'=>false, // 如果你用compose.json 设置加载 app 目录，改为 true;
    
    //// properties ////
    'override_class'=>'Base\App',   
                                // 基类，后面详细说明
    'is_debug'=>false,          // 是否是在开发状态
    'platform'=>'',             //  配置平台标志，Platform 函数得到的是这个
    'reload_for_flags'=>true,   // 从设置文件里重新加载 is_debug,platform 选项
    'enable_cache_classes_in_cli'=>true, 
                                // 命令行下缓存 类数据
    'skip_view_notice_error'=>true,
                                // view 视图里忽略 notice 错误。
    'skip_404_handler'=>false,  // 404 由外部处理。
    'ext'=>[],                  // 扩展
    
    //// error handler ////
    'error_404'=>'_sys/error-404',
                                // 404 页面
    'error_500'=>'_sys/error-500',
                                // 错误页面
    'error_exception'=>'_sys/error-exception',  
                                // 异常页面
    'error_debug'=>'_sys/error-debug',
                                // 调试页面

];
```

其他组件的配置，也可能写在这里。

##### 基本选项
'path'=>null,

    基本路径，其他配置会用到这个基本路径。
'namespace' =>'MY',

    工程的 autoload 的命名空间
'path_namespace'=>'app',

    默认的 psr-4 的工程路径配合 skip_app_autoload  使用。
'skip_app_autoload'=>false

    跳过应用的加载。
'override_class'=>'Base\App',

    **重要选项**
    基于 namespace ,如果这个选项的类存在，则在init()的时候会切换到这个类完成后续初始化，并返回这个类的实例。
    注意到 app/Base/App.php 这个文件的类 MY\Base\App extends DNMVCS\DNMVCS;
    如果以  \ 开头则是绝对 命名空间
'is_debug'=>false,
    配置开发平台 * 设置文件的  platform 会覆盖
'platform'=>'',

'skip_view_notice_error'=>true,

    view 视图里忽略 notice 错误。
'reload_for_flags'=>true,

    从设置里重载 is_debug 和 platform
'skip_404_handler'=>false,

    不处理404，用于你想在流程之外处理404的情况
##### 错误处理

error_* 选项为 null 用默认，为 callable 是回调，为string 则是调用视图。

    error_500 选项 是应对 Error,error_exception 选项是应对 exception
'error_debug'=>'_sys/error-debug',

    is_debug 打开情况下，显示 Notice 错误
'error_404'=>'_sys/error-404'

    404 页面
'error_500'=>'_sys/error-500'

    500 页面
'error_exception'=>'_sys/error-exception'

    excption 页面。

##### "ext" 选项和扩展

ext 是一个选项，这里单独成一节是因为这个选项很重要。涉及到 DNMVCS 的扩展系统

在 DNMVCS/Core 里， ext 是个空数组。

    扩展映射 ,$ext_class => $options。

    $ext_class 为扩展的类名，如果找不到扩展类则不启用。
    
    $ext_class 满足接口。
    $ext_class->init(array $options,$context=null);
    
    如果 $options 为  false 则不启用，
    如果 $options 为 true ，则会把当前 $options 传递进去。


#### HttpServer
    用于构建单独的 Http 服务器

### DMMVCS 目录结构

工程的桩代码,默认目录结构

```text
+---app                     // psr-4 标准的自动加载目录
|   +---Base                // 基类放在这里
|   |   |   App.php         // 默认框架入口文件
|   |   |   BaseController.php  // 控制器基类
|   |   |   BaseModel.php   // 模型基类
|   |   |   BaseService.php // 服务基类
|   |   \---Helper
|   |           ControllerHelper.php    // 控制器助手类
|   |           ModelHelper.php     // 模型助手类
|   |           ServiceHelper.php   // 服务助手类
|   |           ViewHelper.php      // 视图助手类
|   +---Controller          // 控制器目录
|   |       Main.php        // 默认控制器
|   +---Model               // 模型放在里
|   |       TestModel.php   // 测试模型
|   \---Service             // 服务目录
|           TestService.php // 测试 Service
+---bin
|       start_server.php    // 启动 Htttp 服务
+---config                  // 配置文件放这里
|       config.php          // 配置，目前是空数组
|       setting.sample.php  // 设置，去除敏感信息的模板
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

BaseController, BaseModel, BaseService 是你自己要改的基类，基本只实现了单例模式。
ContrllorHelper,ModelHelper,ServiceHelper 如果你一个人偷懒，直接用 APP 类也行  
#### 如何精简目录
* 移除 app/Base/Helper/ 如果你直接用 App::* 替代 M,V,C,S 助手类。
* 移除 app/Base/BaseController.php 如果你的 Controller 和默认的一样不需要基本类。
* 移除 app/Base/BaseModel.php 如果你的 Model 不需要用全静态方法。
* 移除 app/Base/BaseService.php 如果你的 Service 不需要 G 方法。
* 移除 bin/start_server.php 如果你使用外部 http 服务器
* 移除 config/ 在启动选项里加 'skip_setting_file'=>true ，如果你不需要 config/setting.php，
    并有自己的配置方案
* 移除 view/\_sys  你需要设置启动选项里 'error\_404','error\_500','error\_exception' 。

### 入口类 App 类的方法 以及高级程序员。

MY\Base\App 是 继承扩展 DNMVCS\Core\App 类的方法。

DNMVCS\Core\App 类或其子类在初始化之后，会切换入这个子类走后面的流程。

MY\Base\App 重写 override 的两个重要方法

onInit();
    用于初始化，你可能会在这里再次调整  $this->options。
    你可以在调用父类的初始化前后做一些操作
onRun();

    用于运行前，做一些你想做的事
RunQuickly();

{聚合方法}

    ModelHelper,SerivceHelper,ControllerHelper 都在 App 类里有实现。
    这用于你想偷懒，直接 App::foo(); 的情况。
addRouteHook($hook,$prepend=false,$once=true)

    添加路由钩子 
    $hook 返回空用默认路由处理，否则调用返回的回调。

addBeforeShowHandler($callback)

    添加显示前处理
addBeforeRunHandler($callback)

    添加运行前处理
extendComponents($class,$methods,$components);
    扩展组件的静态方法。
    其中： $components 为 ['M','V','C','S'] 组合可选。
其他方法

    其他方法有待你的发掘。如果你要用于特殊用处的话。
    目前一共有 32 个 public function ,36 public static function ,10 protected function 
    还有 +3 来自 ExtendableStaticCallTrait 方法。

#### ExtendableStaticCallTrait

查看有什么额外的静态方法：  GetExtendStaticStaticMethodList();

ExtendStaticCallTrait 也在 M,V,C,S 助手类中有，你可以动态添加。

用 AssignExtendStaticMethod() ，来添加。当然，方便的是。

App::G()->extendComponents($class,$methods,$components);


## 第三章 DNMVCS 核心开发和扩展参考

### 流程

### 其他核心组件选项

本章介绍的是核心扩展的选项，这些选项，可以通过修改 App 类的 $options 里设置。

#### 可变单例 G 方法
这里，对之前的 G 方法统一说明
G 方法表面上是个单例函数，实际上的可替换的。
DNMVCS 系统组件的连接，多是以调用类的可变单例来实现的。

#### 结构图和组件分析
![core](doc/dnmvcs.gv.svg)


DNMVCS/Framework 的扩展都放在 DNMVCS\\Ext 命名空间里
下面按字母顺序介绍这些扩展的作用
按选项，说明，公开方法，一一介绍。

SingletonEx 可变单例

Base\*Helper 是各种快捷方法。

其他组件都遵守 init(array $options, $contetxt=null); 接口。

这些组件 都可以在 onInit 里通过类似方法替换
```php
Route::G(MyRoute::G());
View::G(MyView::G());
Configer::G(MyConfiger::G());
RuntimeState::G(MyRuntimeState::G());
```

例外的是 AutoLoader 和 ExceptionManager 。 这两个是在插件系统启动之前启动
所以你需要：
```php
AutoLoader::G()->cleanUp();
AutoLoader::G(MyAutoLoader::G())->init($this->options,$this);

ExceptionManager::G()->cleanUp();
ExceptionManager::G(MyExceptionManager::G())->init($this->options,$this);
```
如何替换组件。

注意的是核心组件都在 onInit 之前初始化了，所以你要自己初始化。
为什么核心组件都在 onInit 之前初始化。
为了 onInit 使用方便

为什么 Core 里面的都是 App::Foo(); 而 Ext 里面的都是 App::G()::Foo();
因为 Core 里的扩展都是在 DNMVCS\Core\App 下的。

Core 下面的扩展不会单独拿出来用， 
如果你扩展了该方面的类，最好也是让用户通过 App 或者 MVCS 组件来使用他们。

### Core\Autoloader
DNMVCS\AutoLoader 类是 psr-4 加载类。
##### 选项
```
'path'=>null,
'namespace'=>'MY',
'path_namespace'=>'app',

'skip_system_autoload'=>true,
'skip_app_autoload'=>false,
```
##### 示例

### Core\Configer
##### 选项
```
    'path'=>null,
    'path_config'=>'config',    //配置路径目录
    'all_config'=>[],
    'setting'=>[],
    'setting_file'=>'setting',
    'skip_setting_file'=>false,
```
##### 说明
Core\Configer 的选项共享个 path,带个 path_config

path_config 如果是 / 开始的，会忽略 path 选项

    当你想把配置目录 放入 app 目录的时候，调整 path_config
    当我们要额外设置，配置的时候，把 setting , all_config 的值 带入
    当我们不需要额外的配置文件的时候  skip_setting_file 设置为 true
##### 方法

### Core\View
##### 选项
```  
'path'=>null,
'path_view'=>'view',
```
Core\View 的选项共享一个 path,带一个 path_view.

path_view 如果是 / 开始的，会忽略 path 选项

当你想把视图目录 放入 app 目录的时候，调整 path_view
##### 方法

### Core\Route
DNMVCS\Core\Route 这个类可以单独拿出来做路由用。
##### 选项
```
'namespace'=>'MY',
'namespace_controller'=>'Controller',
'controller_base_class'=>null,
'controller_welcome_class'=>'Main',

'controller_hide_boot_class'=>false,
'controller_methtod_for_miss'=>null,
'controller_prefix_post'=>'do_',
'controller_methtod_for_miss'=>'_missing',
```
'controller_base_class'=>null,
    
    限定控制器基类，配合 namespace namespace_controller 选项。
    如果是 \ 开头的则忽略 namespace namespace_controller 选项。
'controller_prefix_post'=>'do_',

    POST 的方法会在方法名前加前缀 do_
    如果找不到方法名，调用默认方法名。
'controller_welcome_class'=>'Main',

    默认欢迎类是  Main 。
'controller_methtod_for_miss'=>'_missing',
    
    如果有这个方法。找不到方法的时候，会进入这个方法
    如果你使用了这个方法，将不会进入 404 。
##### 示例
这是一个单用 Route 组件的例子
```php
<?php
use DNMVCS\Core\Route;
require(__DIR__.'/vendor/autoload.php');

class Main
{
    public function index()
    {
        var_dump(DATE(DATE_ATOM));
    }
    public function i()
    {
        phpinfo();
    }
}
$options=[
    'namespace_controller'=>'\\',
];
$flag=Route::RunQuickly($options);
if(!$flag){
    header(404);
    echo "404!";
}

```

### Core\RunTimeState

### Core\SuperGlobal

### 分割线
    以下介绍的都是 Ext的参考。
### DBManager
默认开启。DBManager 类是用来使用数据库的
M::DB() 用到了这个组件。
#### 选项
    'db_create_handler'=>null,  // 默认用 [DB::class,'CreateDBInstance']
    'db_close_handler'=>null,   // 默认等于 [DB::class,'CloseDBInstance']
    'before_get_db_handler'=>null, // 在调用 DB 前调用
    'use_context_db_setting'=>true, //使用 setting 里的。
    'database_list'=>null,      //DB 列表
db_create_handler
#### 说明

database_list 的示例：
    [[
		'dsn'=>'mysql:host=???;port=???;dbname=???;charset=utf8;',
		'username'=>'???',
		'password'=>'???',
	]],
#### 方法
DB()
    是 App::DB 和 M::DB 的实现。
#### DB 类的用法
DB
    close(); //关闭, 你一般不用关闭,系统会自动关闭
    getPDO(); //获取 PDO 对象
    quote($string);
    fetchAll($sql, ...$args);
    fetch($sql, ...$args);
    fetchColumn($sql, ...$args);
    execQuick($sql, ...$args); //   执行某条sql ，不用 exec , execute 是为了兼容其他类。
#### 示例
使用数据库，在 设置里正确设置 database_list 这个数组，包含多个数据库配置
然后在用到的地方调用 DNMVCS::DB($tag=null) 得到的就是 DNDB 对象，用来做各种数据库操作。
$tag 对应 $setting['database_list'][$tag]。默认会得到最前面的 tag 的配置。

你不必担心每次框架初始化会连接数据库。只有第一次调用 DNMVCS::DB() 的时候，才进行数据库类的创建。

DB 的使用方法，看后面的参考。
示例如下

```php
<?php
use DNMVCS\DNMVCS;
use DNMVCS\Helper\ModelHelper as M;

require_once('../vendor/autoload.php');

$options=[];
$options['override_class']='';      // 示例文件不要被子类干扰。
$options['skip_setting_file']=true; // 不需要配置文件。
$options['error_exception']=true; // 使用默认的错误视图

$options['database_list']=[[
    'dsn'=>'mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;',
    'username'=>'root',
    'password'=>'123456',
]]; // 这里用选项里的
DNMVCS::RunQuickly($options,function(){    
    $sql="select 1+? as t";
    $data=M::DB()->fetch($sql,2);
    var_dump($data);
    DNMVCS::exit_system(0);
});
```
#### 使用 think-orm 的 DB

$options['datbase_list，在启动选项里加skip_setting_file']]; 
// '这里要填充假配置'=>true ，如果你不需要 config/setting.php .
    并有自己的配置方案
* ptiview/_sy\s 。e_ 你需要设置ist，在 'error\_404','error\_500','error\_exception' 。

```php
<?php
use think\facade\Db;
use DNMVCS\Ext\DBManager;
use DNMVCS\DNMVCS;
require_once('../vendor/autoload.php');

$options=[];
$options['override_class']='';      // 示例文件不要被子类干扰。
$options['skip_setting_file']=true;// 不需要配置文件。
$options['error_exception']=true; // 使用默认的错误视图
DNMVCS::RunQuickly($options,function(){
    Db::setConfig([
        'default'     => 'mysql',
        'connections' => [
            'mysql'     => [
                'type'     => 'mysql',
                'hostname' => '127.0.0.1',
                'username' => 'root',
                'password' => '123456',
                'database' => 'DnSample',
            ]
        ]
    ]);
    //就这句话了
    DBManager::G()->setDBHandler(function(){return Db::class;});
    $sql="select * from Users where true limit 1";
    $data=DNMVCS::DB()::query($sql);
    var_dump($data);
    DNMVCS::exit_system(0);
});

```
### DBReusePoolProxy
连接池，默认没开启
    'db_reuse_size' => 100,
    'db_reuse_timeout' => 5,
    'dbm' => null,
### FacadesAutoLoader

你们要的 Facades 伪静态方法
'facades_namespace'=>'Facades', // 前缀
'facades_map'=>[],
#### 示例
```php
use Facades\MY\Model\TestModel;
TestModel::foo(); // <=> \MY\Model\TestModel::G()->foo();
```
### JsonRpcExt
一个 JonsRPC 的示例，安全验证功能 需要加上
#### 默认选项
'jsonrpc_namespace'=>'JsonRpc',
'jsonrpc_backend'=>'https://127.0.0.1', 
//TODO
后端，允许用数组，后面表示是实际IP，用于方便调试，见例子。实际连的是 127.0.0.1。
#### 示例
```php
// Base\App onInit;
$this->options['ext']['Ext\JsonRpcExt']=[
    'jsonrpc_backend'=>['http://test.dnmvcs.dev/json_rpc','127.0.0.1:80'], 
];
```

/////////////
```php
<?php
require_once(__DIR__.'/../vendor/autoload.php');

use DNMVCS\Core\Route;
use DNMVCS\Core\SingletonEx;
use DNMVCS\Ext\JsonRpcExt;
use JsonRpc\CalcService as RemoteCalcService;

class CalcService
{
    use SingletonEx;
    public function add($a,$b)
    {
        return $a+$b;
    }
}

class Main
{
    public function index()
    {
        $t=CalcService::G()->add(1,2);
        var_dump($t);
        
        $t=CalcService::G(JsonRpcExt::Wrap(CalcService::class))->add(3,4);
        var_dump($t);
    }
    public function json_rpc()
    {
        $ret=JsonRpcExt::G()->onRpcCall($_POST);
        echo json_encode($ret);
    }
}
$options=[
    'is_debug'=>true,
    'namespace_controller'=>'\\',
];
JsonRpcExt::G()->init([
    'jsonrpc_namespace'=>'JsonRpc',
    'jsonrpc_backend'=>['http://d.dnmvcs.dev/2.php/json_rpc','127.0.0.1:80'], //请自行修改这里。
    'jsonrpc_is_debug'=>true,
],null);
$flag=Route::RunQuickly($options);
if (!$flag) {
    header(404);
    echo "404!";
}
```
这个例子，将会两次远程调用 http://d.dnmvcs.dev/2.php/json_rpc 的 CalcService 。

这里的 json_rpc 是服务端的实现

如果你要 做自己的权限处理，则重写 protected function prepare_token($ch)。
### Lazybones
懒汉配置。你或许能找到些东西。
    'lazy_mode'=>true,
    'use_app_path'=>true,
    'lazy_path'=>'',// <=> $context->options['path'],
    'lazy_path_service'=>'Service',
    'lazy_path_model'=>'Model',
    'lazy_path_contorller'=>'Controller',
    
    'lazy_controller_class'=>'DNController',
    'with_controller_namespace_namespace'=>true,
    'with_controller_namespace_prefix'=>true,
### Oldbones
一些过时的东西配置。你或许能找到些东西
    'fullpath_project_share_common'=>'',
    'fullpath_config_common'=>'',
    'function_view_head',
    'function_view_foot',
### Pager
分页。只是解决了有无问题，如果有更好的，你可以换之。
为什么DNMVCS 框架要带这么个简单的分页类，因为不想做简单的演示的时候要去找分页处理。
'url'=>null,
'key'=>null,
'page_size'=>null,
'rewrite'=>null,
'current'=>null,
### RouteHookDirectoryMode
多目录模式的 hook
    'mode_dir_index_file'=>'',
    'mode_dir_use_path_info'=>true,
    'mode_dir_key_for_module'=>true,
    'mode_dir_key_for_action'=>true,
### RouteHookOneFileMode
    单一文件模式的 hook
### RouteHookRewrite
默认开启 实现了rewrite 。

rewrite 支持以 ~ 开始表示的正则， 并且转换后自动拼凑 $_GET
#### 选项
    'rewrite_map'=>[],
#### 方法
assignRewrite()
getRewrites()
### RouteHookRouteMap
默认开启,实现了路由映射功能
#### 选项
   'route_map'=>[],
如果是 * 结尾，那么把后续的按 / 切入 parameters
route_map key 如果是 ~ 开头的，表示正则
否则是普通的 path_info 匹配。

支持 'Class->Method' 表示创建对象，执行动态方法。
你可以 
parameters 

#### 方法
assignRoute($route,$callback); 
    是 C::assignRoute 和 App::assignRoute 的实现。
getRoutes()
    dump  route_map 的内容。
### StrictCheck

用于 严格使用 DB 等情况。使得在调试状态下。不能在 Controller 里 使用 M::DB();等

## 第四章 DNMVCS 其他类参考

## 其他

----
####
new , 协程单例 ，单例，static function

效率 static function 最高
new 多个效率比单例 低
协程单例，需要的操作要多。效率底点。
但是协程单例可以防低级错误。
### 本章说明
DNMVCS 的使用者角色分为 应用程序员，和核心程序员两种
应用程序员负责日常 Curd 。核心程序员做的是更高级的任务。



#### SingletonEx
SingletonEx 可变单例， 是 DNMVCS 系的基础

#### DNMVCS/Core
DNMVCS 核心功能。
DNMVCS/Core 目录 实现了一个微型框架。包括文件风格的路由。异常管理。和 Swoole 兼容的准备工作。
独立的带一个 HttpServer 服务器。

#### DNMVCS/Framework
DNMVCS 的常规框架。
比 DNMVCS/Core 多了，数据库的处理，路由重写。
和对 SwooleHttpd 的支持。

#### DNMVCS/SwooleHttpd
SwooleHttpd 是一个 Swoole Http 服务器框架

#### DNMVCS/Plus
对 DNMVCS 的第三方扩展的称呼
如果这些扩展，只用到可变单例， 引入 SingletonEx 这个项目够了。

#### 发布 DNMVCS 的 Composer 命名规范


* DNSingletonEx/SingletonEx
* DNMVCS/Framework

假设你


* Somebody/DNSingleton-foo
你只用到 SingletonEx 这个类。

* Somebody/DNMVCSCore-foo
你只和 DNMVCS/Core 有关
* Somebody/DNMVCS-foo
你需要其他 DNMVCS
