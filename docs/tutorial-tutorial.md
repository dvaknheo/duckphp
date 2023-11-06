# 施工中，markdown 格式混乱

DuckPhp 1.3.1 发布箴言。

DuckPhp 1.3.1 现在发布。经过2年多没动之后，我花了几个月，做了现在的 DuckPhp 的新版本，做了很多改动。

最大的改动的以前插件模式要用专门的模式，现在就不需要。无缝使用第三方DuckPhp Project 作为 library。

解决了 框架不能套框架的问。

添加了容器化， 解决了自己发明的不能重复插入的功能

副作用是同时也引入了 相位 Phase 概念以隔离不同应用，使得简单的变复杂了 :(

因为改动巨大，所以原定的 1.2.13 升级到了 1.3.1 。 1.3 系列。

根据 webman admin 做了另外的  管理后台 duckadmin 在另一个工程里。

和其他 管理后台不同的是， duckadmin 后台是 library ，其他工程可以调用。而且，所有实现都能自由替换。

而 webman admin 以及种种框架的 后台系统，都是要你在后台系统上做二次开发。


## 从使用 DuckAdmin  的 Composer Libaray 开始的 DuckPhp 教程


一般的后台系统都是在上面做二次开发。
我们这回的代码缺一个 后台系统，我们要使用 DuckAdmin 作为我们的后台。
所以，我们在我们的项目里引入了 DuckAdmin

```
composer require dvaknheo/duckadmin
```

然后，在我们的 404 代码处理处加上最基础的版本：

```php
//require_once(__DIR__.'/../../vendor/autoload.php');

$options=[
    'ext'=>[
        DuckAdminApp::class => [
            'controller_url_prefix'=>'admin/',
            //其他配置
            ''on_inited' => 'onDuckAdminInit', //这里演示初始化插一个东西
        ],
        DuckUserApp::class => [
            'controller_url_prefix'=>'user/',
            //其他配置
        ],
        SimpleBlogApp::class => [
            'controller_url_prefix'=>'blog/',
            //其他配置
        ],
    ],
    'is_debug'=>true, //
    //其他配置
    
    'on_inited' => 'onInit',
    
];
$welcom_callback =function(){
  echo "hello",
};

$flag =\DuckPhp\DuckPhp::InitAsContainer($options)->thenRunAsContainer(false, $welcome_callback);


function onInit()
{
    // 这个回调在初始化阶段可以做很多事
    //return;
}
function onDuckAdminInit()
{
}

```
接下来我们就可以访问
`/admin/` 得到duckadmin 的页面了。
## 二次开发
1. 修改配置实现
    正如演示看到的，每个子应用和主应用都有自己的配置选项。
2. 修改页面， 默认的 view 太难看，我们要覆盖 override 改成自己的：
    `[工程文件夹]/view/DuckAdmin/【同名文件】` 
    
3.页面里可以用到 全局函数,这些全局函数都是两个下划线开始的。

这是助手函数

    function __h(...$args) html 编码
    function __l($str, $args = []) 多语言编码
    function __hl($str, $args = []) Html加多语言编码
    function __json($data)   json 编码，用于向 javascript  传送数据
    function __url($url)   相对pathinfo 的路径
    function __res($url)   资源文件
    function __domain($use_scheme = false)  域名，带头
    function __display($view, $data)  实现包含的 View 块用于视图种
还有一批调试用的全局函数

    function __platform()  用于多服务器配置的时候看处于什么服务器
    function __is_debug()  调试状态是否开启
    function __is_real_debug() 调试状态关闭，但一些东西需要调试的时候用
    function __var_log($var)  在日志打印当前变量
    function __var_dump(...$args) 接管var_dump
    function __trace_dump()  打印堆栈
    function __debug_log($str, $args = []) 增加日志
    function __logger() 获得 日志对象，便于不同级别的调试
所有 DuckPhp 的全局函数就这么讲完了 ^_^

3. 获取提供对象
你可以在代码里得到管理员对象和 用户对象。 用于你的业务系统
如果得不到，将会抛出异常，你可以作后续处理。

```
$id = DuckAdminApp::AdminId();
$admin = DuckAdminApp::Admin()->current(); // 获取当前 admin 对象，如果得不到 admin 对象，则会跳转登录
var_dump($admin);

```

4. 热修复，修改实现

假设我们对他哪个实现不满意。
```php
function onInit(){
  DuckPhp::Phase(DuckAdminApp::class); //切入要修改的子应用的相位。
  UserAction::_(MyUserAction::_());   //修改单例。这里是你的 UserAction 由你实现。
}
```
致此，二次开发基本讲完了。要深入了解，那么我们就从自己搞个工程开始了

## 新建工程的目录结构
我们建立工程文件夹，然后用 composer 建立新的 DuckPhp 工程
```
composer require dvaknheo/duckphp # 用 require 
./vendor/bin/duckphp new --help   # 查看有什么指令
./vendor/bin/duckphp new    # 创建工程
```


在这里，我们用 `tree`  列一下工程的文件结构
```
.
├── config
│   ├── DuckPhpApps.config.php
│   └── DuckPhpSettings.config.php
├── public
│   └── index.php
├── runtime
│   └── keepme.txt
├── src
│   ├── Business
│   │   ├── Base.php
│   │   ├── BusinessException.php
│   │   ├── CommonService.php
│   │   ├── DemoBusiness.php
│   │   └── Helper.php
│   ├── Controller
│   │   ├── Base.php
│   │   ├── CommonAction.php
│   │   ├── ControllerException.php
│   │   ├── ExceptionReporter.php
│   │   ├── Helper.php
│   │   ├── MainController.php
│   │   ├── Session.php
│   │   └── testController.php
│   ├── Model
│   │   ├── Base.php
│   │   ├── CrossModelEx.php
│   │   ├── DemoModel.php
│   │   └── Helper.php
│   └── System
│       ├── App.php
│       ├── Helper.php
│       └── ProjectException.php
└── view
    ├── _sys
    │   ├── error_404.php
    │   └── error_500.php
    ├── files.php
    ├── main.php
    └── test
        └── done.php


```

## 目录结构解说
小写的是资源文件夹，资源文件夹可以由 $options['path']设置为其他目录。
### 工程文件夹
* config 配置文件夹。通过修改选项，也可以不需要这个文件夹
    * `config/DuckPhpSettings.config.php` 这个文件是存在的 ，只有根应用会有用，作用是保存设置的。
    * `config/DuckPhpApps.config.php` 这个是选项文件子应用的额外选项都在这里。安装的时候，会改写这个文件。
* public web入口
    * `index.php` 传统的web入口
* runtime 文件夹是唯一需要可写的文件夹。默认工程没有写入东西。
    * keepme.txt 只是 git 作用
* src 类文件夹。工程代码文件。
   后面详解
* view 视图文件夹
   * view/_sys/error404.php 404 错误展示页面
   * view/_sys/error404.php 500 错误展示页面
   * view/files.php 对应访问 file 的页面文件
   * view/main.php 对应 访问 默认 的页面文件
   *view/test/done.php 对应访问 test/done 的页面文件
   
   V 所以 DuckPhp 保持 PHP 就是模板的简洁性（本人曾经有个没人用的TagFeather 模板，说不定某年复活。
----
### src 源代码目录

一共4个目录 我们以字母顺序 调用顺序 System -> Controrller -> Business -> Model 来介绍


#### Base.php & Helper.php
每个目录下都有 Base.php & Helper.php
Base 基类， 不带，后缀是基类的规范。


Helper 类都是和业务无关的类。通过这些Helper类的静态方法来调用 DuckPhp 系统的功能。 
所有 Helper 默认都只有静态方法
你自己的 Helper请用动态方法以表示区别。

一共4个Helper 类,前两个比较简单。

Controller/Helper 方法比较复杂 你需要查相关文档，做的都是和 web相关的一堆东西
System/Helper 也很需要单独说明了。


#### Model

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace AdvanceDemo\Model;

use DuckPhp\Foundation\SimpleModelTrait;

class Base
{
    use SimpleModelTrait;
}

```

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace AdvanceDemo\Model;

use DuckPhp\Foundation\SimpleSingletonTrait;
use DuckPhp\Helper\ModelHelperTrait;

class Helper
{
    use SimpleSingletonTrait;
    use ModelHelperTrait;
}

```
Model/Helper 方法只有 Db() DbForRead() DbForWrite() SqlForPager() SqlForCountSimply()

其实，偷懒的时候，这几个都可以合并在一起。

DuckPhp 的 Model 层是很传统的跟着数据库表名走的模式。

XX-Model.php 这是示例 Demo ，你可以删除他根据你的数据库表重建

YY-ModelEx.php 这是示例 跨表 Demo ，你可以删除他重建

#### Business 

作为程序员专家，大家达成的意见是 业务逻辑层要抽出来，业务逻辑 英文是什么 Business Logic 嘛。

有人用Logic ，这里我用的是 Business 命名 还有人用 Service。

需要注意的是，虽然有人把这层独立出来，但是代码里却是和 web相关， Business 要求是什么，和Controller 无关，无状态。可测。

当然，有些人会带上用户 ID ，这种一两个的例外。

相比 Model 目录，这里多了 BusinessException 。 因为规范要求 model 类不得抛异常

BusinessException.php 默认异常类

Helper.php 方法有 Setting() Config() XpCall() FireEvent() Cache() OnEvent()

Business 按规范，也有个 Base 公用基类

xx-Service.php  

Business 之间相互调用的业务半成品，那么就抽出成 Service。

Business 相互调用，则放到 Service 里，这就是 Business 层不用 Service 来命名的原因

xx-Business.php  你可以删除

#### Controller

Web的入口就是控制器， DuckPhp 理念里，Controller 只处理web入口。 业务层由 Business 层处理。

Base.php 控制器类

Helper.php 控制器助手类

ControllerException.php 控制器层的异常类

ExceptionReporter.php 则是处理各种错误。

Session.php Session 处理相关


xx-Action.php

Controller 调用 Controller 怎么办。 DuckPhp 的规范是 Controller 不要调用 Controller

把 部分逻辑 放为 Action。 用 Controller 调用 Action.

其他业务相关 xx-Controller.php



#### System
 * App.php 入口位置
 * Helper.php
 * ProjectException.php


```php
class App extends DuckPhp
{
    //@override
    public $options = [
        //'is_debug' => true, // debug switch
        // 'setting_file_enable' => true,
        //'path_info_compact_enable' => false,
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        'controller_class_postfix' => 'Controller', // in common, use this
        
    ];
    /**
     * console command sample
     */
    public function command_hello()
    {
        echo "hello\n";
    }
    public function action()
    {
        //
    }
    public function service()
    {
        //
    }
    
}
```
## 代码分析
首先我们从入口文件 public/index.php 开始，这个文件内容很简单：

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../../../autoload.php');    //@DUCKPHP_HEADFILE

echo "<div>You should not run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行模板文件，建议用安装模式 </div>\n";              //@DUCKPHP_DELETE

\DuckPhp\Core\AutoLoader::RunQuickly([]);
\DuckPhp\Core\AutoLoader::addPrs('../src', 'AdvanDemo\\');    

$options = [
    // ...
];

\AdvanDemo\SysemApp::RunQuickly($options);

```
$options 是就是各种选项了。



-------------
















## 理解相位
1.2.13 版本，我们为每个子应用做了相位隔离。不同子应用
所以 DuckAdmin 的 Route::_() 就和 DuckUser 的 Route::_() 是不同实例了。
相位是以主类作为 命名空间隔离的。
切入相位， 共享相位的单位， 内容的 dump
## 调用

DuckAdmin\System\UserApi::CallInPhase($phase)->foo();
Admin 和 User 这两个特殊
MyApp::AdminId();
MyApp::Admin()->data();
MyApp::User()->data();
## 替换
DuckAdmin::PhaseCall(DuckAdmin::class,function(){AdminUser::_(MyAdminUser::_());});

/////////////////////////////////////////////////////////////////////////
```
带 *的为重复项
AutoLoader
DuckPhpAllInOne
  --
  |\
DuckPhp
    GlobalAdmin
        * SingletonTrait
    Cache
    CallInPhaseTrait
        * PhaseProxy
    Configer
    DbManager
        * Logger;
        * Db;
    DuckPhpCommand
        * Console
    DuckPhpInstaller
    EventManager
    ExtOptionsLoader
        * App
    Pager
        * PagerInterface
    PhaseProxy
    RedisCache
    RedisManager
    RouteHookPathInfoCompat
        * Route
    RouteHookResource
        * Route
    RouteHookRewrite
        * App
    RouteHookRouteMap
        * Route
    GlobalUser
        * SingletonTrait
    App
        ComponentBase
            ComponentInterface
            SingletonTrait
                * PhaseContainer
        DuckPhpSystemException
            ThrowOnTrait
        KernelTrait
            Console
            * Route
            ExceptionManager
            Functions
            PhaseContainer
            Route
            Runtime
            View
        Logger
        * Route
        * Runtime
        SuperGlobal
        SystemWrapper
Helper
    * App
    * Logger
    * Route
    * SystemWrapper
```


其他隐藏要素


-------------
备用

### MVC 缺层


首先，还是问写过几年php web开发的，MVC 架构有什么缺陷？
答案是缺层，controller 一股脑包的代码太多太恶心了。
所以我们改成 VCBM 结构

但是，这只是理想中的  VCBM
实践起来， vcbm 结构会碰到什么问题下面详细说吧

我们从简单到到复杂

首先，我们上理论课——MVC 缺层...MVC 结构，大家都滚瓜烂熟了。

如果我们做一个 PHP 项目，MVC结构
最简单的
```
 /-M
C
 \-V
```
M 对应的数据库。 C 对应的和 URL。 V 对应的是视图，给前端用的。嗯。
好，那么业务逻辑写在哪里？

所以就引发了很多灾难。
充血模型， 写在 C 端。 等等。

所以用 DuckPhp 的，就把这模型改了一下，加上缺失的业务层，变成了：

```
 /-B-M
C
 \-V
```

复杂一点 复杂一点 再加上事件和异常

V View 这很容易理解。当年 Smarty 引领了一个时代，但是到最后， php 程序员发现还得自己写 smarty 代码
所以 DuckPhp 保持 PHP 就是模板的简洁性（本人曾经有个没人用的TagFeather 模板，说不定某年复活。
