# DuckPHP 教程
## 第一章 快速入门
### 安装
假定不管什么原因，选用了 DuckPHP 这个框架，需要快速入门.

最快的方式是从 github 下载 DuckPHP。

到所在目录之下运行

```bash
php template/bin/start_server.php
```
浏览器中打开 http://127.0.0.1:8080/ 得到下面欢迎页就表明 OK 了
```text
Don't run the template file directly
Hello DuckPHP

Time Now is [2019-04-19T21:36:06+08:00]
For More Take the DuckPHP-FullTest (TODO)
```
发布的时候，把网站目录指向 public/index.php 就行。
### 另一种安装模式： Composer 安装
在工程目录下执行：
```
composer require dvaknheo/duckphp # 用 require 
./vendor/bin/duckphp --help     # 查看有什么指令
./vendor/bin/duckphp --create   # --full # 创建工程
./vendor/bin/duckphp --start    # --host=127.0.0.1 --port=8080 # 开始 web 服务器
```
将会直接把 template 的东西复制到工程并做调整，同样执行
```bash
php bin/start_server.php
```
浏览器中打开 http://127.0.0.1:8080/ 得到下面欢迎页就表明 OK 了
```text
Don't run the template file directly
Hello DuckPHP

Time Now is [2019-04-19T21:36:06+08:00]
For More Take the DuckPHP-FullTest (TODO)
```
细则可以看 --help 参数

当然你也可以用 nginx 或apache 安装。
nginx 把 document_root 配置成 `public` 目录。

```
try_files $uri $uri/ /index.php$request_uri;
```
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
use MY\Model\MiscModel;

class MiscService  extends BaseService
{
    public function foo()
    {
        $time=MiscModel::G()->getTime();
        $ret="<".$time.">";
        return $ret;
    }
}
```
BaseService 也是不强求的，我们 extends BaseService 是为了能用 G 函数这个单例方法。

这里调用了 MiscModel 。

### Model 模型

完成 MiscModel 。

Model 类是实现基本功能的。

```php
<?php
// app/Model/MiscModel.php
namespace MY\Model;

use MY\Base\BaseModel;
use MY\Base\Helper\ModelHelper as M;

class MiscModel extends BaseModel
{
    public function getTime()
    {
        return DATE(DATE_ATOM);
    }
}
```
同样 BaseModel 也是不强求的，我们 extends BaseModel 是为了能用 G 函数这个单例方法。

### 最后显示结果
```text
test

<2019-04-19T22:21:49+08:00>
```
### 如果没有配置 PATH_INFO
如果你懒得配置 PATH_INFO，把 `public/index.php` 文件这项打开
```php
$options['ext']['DuckPhp\\Ext\\RouteHookeOneFile']=true;
```
同样访问  http://127.0.0.1:8080/index.php?_r=test/done  也是得到想同测试页面的结果

### 数据库操作
前提工作，我们注释掉 `public/index.php` 中跳过设置文件的选项
```php
//$options['skip_setting_file']=true;
```
`./vendor/bin/duckphp --create` 脚本会删去这一行。

数据库演示需要数据库配置。

我们复制 `config/setting.sample.php` 为 `config/setting.php`
```php
return [
    'duckphp_is_debug' => false,
    'duckphp_platform' => 'default',
    //*
    'database_list' => [
        [
        'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8mb4;',
        'username' => 'admin',
        'password' => '123456',
        'driver_options' => [],
        ],
    ],
    //*/
];
```
然后，我们写 `app/Controller/dbtest.php` 如下
```php
namespace MY\Controller;
use MY\Base\App as M;

class dbtest
{
    public function main()
    {
        $ret = $this->foo();
        var_dump($ret);
    }
    public function foo()
    {
        if (M::DB()===null) {
            var_dump("No database setting!");
            return;
        }
        $sql = "select 1+? as t";
        $ret = M::DB()->fetch($sql,2);
        return $ret;
    }
}
```
访问   http://127.0.0.1:8080/dbtest/main 
会得到

```
array('t'=>3);
```
### Tip 开发人员角色

DuckPHP 的使用者角色分为 应用程序员，和核心程序员两种。

应用程序员负责日常 Curd 。核心程序员做的是更高级的任务。

作为应用程序员， 你不能引入 DuckPHP 的任何东西，就当 DuckPHP 命名空间不存在。

核心程序员才去研究 DuckPHP 类里的东西。

### 工程完整架构图


你的工程应该是这么架构。

![arch_full](arch_full.gv.svg)

文字版
```text
           /-> View-->ViewHelper
Controller --> Service ------------------------------ ---> Model
         \         \   \               \  /                  \
          \         \   \-> LibService ----> ExModel----------->ModelHelper
           \         \             \                
            \         ---------------->ServiceHelper
             \-->ControllerHelper
```

同级之间的东西不能相互调用

* 写 Model 你可能要引入 MY\Base\Helper\ModelHelper 助手类别名为 M 。
* 写 Serivce 你可能要引入 MY\Base\Helper\SerivceHelper 助手类别名为 S 。
* 写 Controller 你可能要引入 MY\Base\Helper\ControllerHelper 助手类别名为 C 。
* 写 View 你可能要引入 MY\Base\Helper\ViewHelper 助手类别名为 V 。
* 不能交叉引入其他层级的助手类。如果需要交叉，那么你就是错的。
* 小工程可以用直接使用入口类 MY\Base\App 类，这包含了上述类的公用方法。

ContrllorHelper,ModelHelper,ServiceHelper 如果你一个人偷懒，直接用 APP 类也行  

助手类教程在这里 [助手类教程](tutorial-helper.md)


接下来我们就可以看工程目录结构了。

### DuckPHP 工程目录结构

template 目录就是我们的工程目录示例。也是工程桩代码。


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
\---public                  // 网站目录
        index.php           // 主页，入口页
```

app 目录，就是放 MY 开始命名空间的东西了。
命名空间 MY 是 可调的。比如调整成 MyProject ,TheBigOneProject  等。


文件都不复杂。基本都是空类或空继承类，便于不同处理。
这些结构能精简么？
可以，你可以一个目录都不要。

BaseController, BaseModel, BaseService 是你自己要改的基类，基本只实现了单例模式。
ContrllorHelper,ModelHelper,ServiceHelper 如果你一个人偷懒，直接用 APP 类也行  
#### 如何精简目录
* 移除 app/Base/Helper/ 如果你直接用 App::* 替代 M,V,C,S 助手类。
* 移除 app/Base/BaseController.php 如果你的 Controller 和默认的一样不需要基本类。
* 移除 app/Base/BaseModel.php 如果你的 Model 用的全静态方法。
* 移除 app/Base/BaseService.php 如果你的 Service 不需要 G 方法。
* 移除 bin/start_server.php 如果你使用外部 http 服务器
* 移除 config/ 在启动选项里加 'skip_setting_file'=>true ，如果你不需要 config/setting.php，
    并有自己的配置方案
* 移除 view/\_sys  你需要设置启动选项里 'error\_404','error\_500'。
* 移除 view 如果你不需要 view ，如 API 项目。
* 移除 TestService.php ， TestModel.php  测试用的东西


----


## 第三章 DuckPHP 应用核心开发人员参考

### 入口文件
我们看入口类文件 public/index.php

```php
<?php
require(__DIR__.'/../vendor/autoload.php');

$path=realpath(__DIR__.'/..');
$options=[];

$options['path']=$path;
$options['namespace']='MY';
\DuckPHP\App::RunQuickly($options, function () {
});
// \DuckPHP\App::G()->init($options)->run();
// var_export(\DuckPHP\App::G()->options);
```

入口类前面部分是处理头文件的。
然后处理直接 copy 代码提示，不要直接运行。
起作用的主要就这句话
```php
\DuckPHP\App::RunQuickly($options, function () {
});
```
相当于后面调用的 \DuckPHP\App::G()->init($options)->run(); 第二个参数的回调用于 init 之后执行。

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
    'use_flag_by_setting'=>true,   // 从设置文件里重新加载 is_debug,platform 选项
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

**还有众多其他组件的配置，这里不一一展示。**

##### 基本选项
'skip_setting_file'=> false,

    新手之一最容易犯的错就是，没把这项设置为 true.
    这个选项的作用是跳过读取 setting.php  敏感文件。
    为什么要这么设置， 防止传代码上去而没传设置文件。
    造成后面的错误。
'path'=>null,

    基本路径，其他配置会用到这个基本路径。
'namespace' =>'MY',

    工程的 autoload 的命名空间，和很多框架限定只能用 App 作为命名空间不同，DuckPHP 允许你用不同的命名空间
'path_namespace'=>'app',

    默认的 psr-4 的工程路径配合 skip_app_autoload  使用。
'skip_app_autoload'=>false

    跳过应用的加载，如果你使用composer.json 来加载你的工程命名空间，你可以打开这个选项。
'override_class'=>'Base\App',

**重要选项**

    基于 namespace ,如果这个选项的类存在，则在init()的时候会切换到这个类完成后续初始化，并返回这个类的实例。
    注意到 app/Base/App.php 这个文件的类 MY\Base\App extends DuckPHP\App;
    如果以  \ 开头则是绝对 命名空间
'is_debug'=>false,

    配置是否在调试状态。
'platform'=>'',

    配置开发平台 * 设置文件的  platform 会覆盖
'skip_view_notice_error'=>true,

    view 视图里忽略 notice 错误。
'use_flag_by_setting'=>true,

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


##### "ext" 选项和扩展

ext 是一个选项，这里单独成一节是因为这个选项很重要。涉及到 DuckPHP 的扩展系统

在 DuckPHP/Core/App 里， ext 是个空数组。
**重要选项**

    扩展映射 ,$ext_class => $options。
    
    $ext_class 为扩展的类名，如果找不到扩展类则不启用。
    
    $ext_class 满足接口。
    $ext_class->init(array $options,$context=null);
    
    如果 $options 为  false 则不启用，
    如果 $options 为 true ，则会把当前 $options 传递进去。
DuckPHP/Core 的 Configer, Route, View, AutoLoader 默认都在这调用


### 入口类 App 类的方法 以及高级程序员。

MY\Base\App 是 继承扩展 DuckPHP\App 类的方法。

DuckPHP\App 类或其子类在初始化之后，会切换入这个子类走后面的流程。

DuckPHP\App 包含所有助手类的方法。所有助手方法都在 DuckPHP\App 里实现

#### 用于 override 的两个重要方法

onInit()

    用于初始化，你可能会在这里再次调整  $this->options。
    你可以在调用父类的初始化前后做一些操作
onRun(): void

    用于运行前，做一些你想做的事 ，和 onInit 不同，你不用调用父类
RunQuickly(): bool

    前面已经介绍
#### 聚合方法

    ModelHelper,SerivceHelper,ControllerHelper,ViewHelper 都在 App 类里实现。
    这用于你想偷懒，直接 App::foo(); 的情况。
#### 接管的静态方法
    App::On404();
    App::OnException(): void
    App::OnDevErrorHandler():void 
    App::IsRunning();
    App::InException();

#### 常用方法
这些方法不能归入 助手类里，只能在 App 类单独给出的。

addRouteHook($hook,$position,$once=true)

    添加路由钩子 
    $hook 返回空用默认路由处理，否则调用返回的回调。
    $position 包括位置['prepend-outter','prepend-inner',]
addBeforeShowHandler($callback)

    添加显示前处理
extendComponents($class,$methods,$components);

    扩展组件的静态方法。
    其中： $components 为 ['M','V','C','S','A'] 组合可选。
#### 公开动态方法

    App->init();
    App->run();
    
    App->extendComponents();
    App->assignPathNamespace();
    App->addRouteHook();
#### 内部可扩展方法
    App->initOptions();
    App->checkOverride();
    App->initExtentions();
    App->reloadFlags();
    App::system_wrapper_get_providers();
    App::session_set_save_handler();
#### 下划线开始的动态方法
    下划线开始的动态方法，表示的是内部，但特殊情况下会被外部调用的方法。
    有些会对应无下划线的静态方法，用于实现。 用于接管的状态。


#### 其他方法

    其他方法有待你的发掘。如果你要用于特殊用处的话。
    目前一共有 32 个 public function ,36 public static function ,10 protected function 
    还有 +3 来自 ExtendableStaticCallTrait 方法。
    79 个方法。

### 请求流程和生命周期
DuckPHP\App::RunQuickly($options) 发生了什么

DuckPHP\App::G()->init($options,$callback)->run();

init 为初始化阶段 ，run 为运行阶段。$callback 在init() 之后执行（也是为了偷懒

init 出事化阶段
    处理是否是插件模式
    处理自动加载  AutoLoader::G()->init($options, $this)->run();
    处理异常管理 ExceptionManager::G()->init($exception_options, $this)->run();
    如果有子类，切入子类继续 checkOverride() 
    调整补齐选项 initOptions()
    

    * onInit()，可 override 处理这里了。
    默认的 onInit
        初始化 Configer
        从 Configer 再设置 是否调试状态和平台 reloadFlags();
        初始化 View
        设置为已载入 View ，用于发生异常时候的显示。
        初始化 Route
        初始化扩展 initExtentions()
    初始化阶段就结束了。

run() 运行阶段

    处理 addBeforeRunHandler() 引入的 beforeRunHandlers
    * onRun ，可 override 处理这里了。
    重制 RuntimeState 并设置为开始
    绑定路由
    ** 开始路由处理 Route::G()->run();
    如果返回 404 则 On404() 处理 404
    clear 清理
        如果没显示，而且还有 beforeShowHandlers() 处理（用于处理 DB 关闭等
        设置 RuntimeState 为结束

## 第三章 DuckPHP 核心开发和扩展参考

#### 可变单例 G 方法
这里，对之前的 G 方法统一说明
G 方法表面上是个单例函数，实际上的可替换的。
DuckPHP 系统组件的连接，多是以调用类的可变单例来实现的。

### 结构图
![core](DuckPHP.gv.svg)

##### 架构分析

Core 目录 核心框架

##### Tip 虚拟接口 组件类

组件类满足以下虚拟接口

```
interface
{
    public $options;/* array() */;
    public static function G():this;
    public init(array $options, $contetxt=null):this;
}
```


DuckPHP 的扩展都放在 DuckPHP\\Ext 命名空间里
下面按字母顺序介绍这些扩展的作用
按选项，说明，公开方法，一一介绍。

SingletonEx 可变单例

\*Helper 是各种快捷方法。


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
AutoLoader::G()->clear();
AutoLoader::G(MyAutoLoader::G())->init($this->options,$this);

ExceptionManager::G()->clear();
ExceptionManager::G(MyExceptionManager::G())->init($this->options,$this);
```
如何替换组件。

注意的是核心组件都在 onInit 之前初始化了，所以你要自己初始化。
* 为什么核心组件都在 onInit 之前初始化。

为了 onInit 使用方便

* 为什么 Core 里面的都是 App::Foo(); 而 Ext 里面的都是 App::G()::Foo();
因为 Core 里的扩展都是在 DuckPHP\Core\App 下的。

Core 下面的扩展不会单独拿出来用， 
如果你扩展了该方面的类，最好也是让用户通过 App 或者 MVCS 组件来使用他们。


##### 
    ExtendableStaticCallTrait
    ThrowOn  提供了 实用的 ThrowOn
    HookChain  提供 链式钩子。
    HttpServer 提供了 HttpServer 的实现。
    SystemWrapper 用于 同名函数替代系统系统函数。 比如 header();

