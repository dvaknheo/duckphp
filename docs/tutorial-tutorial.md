# 施工中，markdown 格式混乱

## 前言

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
    //在 amdin 开始的时候搞很多事
}

```
接下来我们就可以访问
`/admin/` 得到 duckadmin 的页面了。
## 二次开发
1. 修改配置实现
    正如演示看到的，每个子应用和主应用都有自己的配置选项。
2. 修改页面， 默认的 view 太难看，我们要覆盖 override 改成自己的：
    `[工程文件夹]/view/DuckAdmin/[同名文件]` 
    
3.页面里可以用到 全局函数,这些全局函数都是两个下划线开始的。

这是助手函数
__h()

    对应 CoreHelper::H(); HTML 编码
__l($str,$args=[])

    对应 CoreHelper::L(); 语言处理函数，后面的关联数组替换 '{$key}'
__hl($str, $args=[])

    对应 CoreHelper::Hl(); 对语言处理后进行 HTML 编码
__json($data) 

    对应 CoreHelper::Hl(); json 编码，用于向 javascript  传送数据
__url($url)

    对应 CoreHelper::Url(); 获得相对 url 地址

__res($url)

    对应 CoreHelper::Res(); 获得资源相对 url 地址
__domain()

    对应 CoreHelper::Domain(); 获得带协议头的域名
__display($view, $data = null)

    对应 CoreHelper::Display(); 包含下一个 $view ， 如果 $data = null 则带入所有当前作用域的变量。 否则带入 $data 关联数组的内容。用于嵌套包含视图。



还有一批调试用的全局函数
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



function __var_log($var)  在日志打印当前变量
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
  $phase = DuckPhp::Phase(DuckAdminApp::class); //切入要修改的子应用的相位。
  UserAction::_(MyUserAction::_());   //修改单例。这里是你的 UserAction 由你实现。
  DuckPhp::Phase($phase);
}
```
致此，二次开发基本讲完了。要深入了解，那么我们就从自己搞个工程开始了

## 新建工程的目录结构
我们建立工程文件夹，然后用 composer 建立新的 DuckPhp 工程
```
composer require dvaknheo/duckphp # 用 require 
./vendor/bin/duckphp new --help   # 查看有什么指令
./vendor/bin/duckphp new          # 创建工程
```


在这里，我们用 `tree`  列一下工程的文件结构
```
tree -I 'public'
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
* runtime 文件夹是唯一需要可写的文件夹。默认工程没有写入东西。
    * keepme.txt 只是 git 作用
* src 类文件夹。工程代码文件。
   后面详解
* view 视图文件夹
   * view/_sys/error404.php 404 错误展示页面
   * view/_sys/error404.php 500 错误展示页面
   * view/files.php 对应访问 file 的页面文件
   * view/main.php 对应 访问主页的页面文件
   *view/test/done.php 对应访问 test/done 的页面文件
----
注意到我们排除了 public 目录,因为默认下带了很多示例文件
我们主要关心入口文件 index.php 他长的是这样子：


```
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');    //@DUCKPHP_HEADFILE
echo "<div>You should not run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行模板文件，建议用安装模式 </div>\n";              //@DUCKPHP_DELETE

// 设置工程命名空间对应的目录，但强烈推荐修改 composer.json 使用 composer 加载 
if (!class_exists(\AdvanceDemo\System\App::class)) {
    \DuckPhp\Core\AutoLoader::RunQuickly([]);
    \DuckPhp\Core\AutoLoader::addPsr4("AdvanceDemo\\", 'src'); 
}

$options = [
    // 这里可以添加更多选项
    //'is_debug' => true,
];
\AdvanceDemo\System\App::RunQuickly($options);
```
入口很简单，就是 Runqucikly ,把 选项数组带进去就是
选项数组可以填什么，看配置
然后，我们入口是 `\AdvanceDemo\System\App` 类，就是后面的内容
   
### src 源代码目录

一共4个目录 我们以字母顺序 调用顺序 System -> Controrller -> Business -> Model 来介绍

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
你可以看到这里也有个 $options ，这里的 $options 和RunQuickly 的 options 合并一起， App->$option 会覆盖

```php
namespace AdvanceDemo\Controller;

use DuckPhp\Foundation\SimpleSingletonTrait;
use DuckPhp\Helper\AppHelperTrait;

class Helper
{
    use SimpleSingletonTrait;
    use AppHelperTrait;
}
```
```php
<?php declare(strict_types=1);
namespace AdvanceDemo\System;

use DuckPhp\Foundation\ExceptionTrait;

class ProjectException
{
    use SimpleExceptionTrait;
}
```


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
Model/Helper 方法只有下面五个


    public static function Db($tag = null)
获得 Db 对象
参见  [DuckPhp\Component\DbManager::Db](Component-DbManager.md#Db)

    public static function DbForRead()
获得只读用的 Db 对象 public static function DbForRead() 
参见 [DuckPhp\Component\DbManager::DbForRead](Component-DbManager.md#DbForRead)

    public static function DbForWrite()
获得读写用的 Db 对象
参见 [DuckPhp\Component\DbManager::DbForWrite](Component-DbManager.md#DbForWrite)

    public static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string
分页 limit 的 sql,补齐 sql用

    public static function SqlForCountSimply(string $sql): string
简单的把 `select ... from ` 替换成 `select count(*)as c from ` 用于分页处理。


偷懒的时候，Helper 和 Base 合并在一起。

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

Helper.php 方法一共有七个方法。

BusinessHelper 用于业务层。三个配置相关方法，两个事件方法，和两个其他方法。

    public static function Setting($key)
获得设置信息

    public static function Config($key =null , $default = null $file_basename = 'config')
获得配置,如果没有则为 default ，如果key 也没有，则是配置文件（默认为 config）所有配置


    public static function FireEvent($event, ...$args)
触发事件

    public static function OnEvent($event, $callback)
绑定事件

    public static function Cache($object = null)
获得缓存对象

    public static function XpCall($callback, ...$args)
调用，如果产生异常则返回异常，否则返回正常数据

ThrowByFlag


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

action_ 前缀的公开方法，是对应的路由调用方法


## 代码分析




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

#### 概念速读

门面， DuckPhp 用可变单例代替了门面
中间件， DuckPhp 提供了简单的中间件扩展 MyMiddlewareManager，但不建议使用。

事件，见事件这一篇
 
请求和响应， DuckPhp 没这个概念 但在 控制器助手类里有很多相同的行为

数据库 ，DuckPhp 的数据库没那么强大

模型 

视图 DuckPhp 的视图原则

错误处理
日志  __logger() 得到 psr 日志类， Logger 类

验证， DuckPhp 没验证处理，你需要第三方类

缓存  Cache()

Session  集中化管理

Cookie  集中化管理

多语言 重写 __l 函数

上传 无特殊的上传

命令行  见命令行的教程，和 DuckPhp\Core\Console 参考类

扩展库

#### FAQ

Q _()方法是不是糟糕了

你可以把 ::_()-> 看成和 facades 类似的门面方法。
可变单例是 DuckPhp 的核心。
你如果引入第三方包的时候，不满意默认实现，可以通过可变单例来替换他

var_dump(MyClass::——()); 使用 Facades 就没法做到这个功能。

Q 为什么不直接用 DB 类，而是用 DbManager

A 做日志之类的处理用

Q 为什么名字要以 *Model *Business *Controller 结尾
让单词独一无二，便于搜索

Q 为什么是 Db 而不是 DB 。
A 为了统一起来。  缩写都驼峰而非全大写

Q 回调 Class::Method Class@Method Class->Method 的区别

A
-> 表示 new 一个实例
@ 表示 $class::_()->

:: 表示 Class::Method

~ => 扩充到当前命名空间

