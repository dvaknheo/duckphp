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

而 webman admin 以及各种框架的 后台系统，都是要你在后台系统上做二次开发。


## 从 DuckAdmin 开始的 DuckPhp 教程


一般的后台系统都是在上面做二次开发。
我们这回的代码缺一个 后台系统，我们要使用 DuckAdmin 作为我们的后台。
所以，我们在我们的项目里引入了 DuckAdmin 这个 composer library

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
            'on_inited' => 'onDuckAdminInit', //这里演示初始化插一个东西
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
    //在 amdin 开始的时候做很多事
}

```
接下来我们就可以访问
`/admin/` 得到 duckadmin 的页面了。
## 对 DuckAdmin 二次开发
1. 修改配置选项实现
    正如演示看到的，每个子应用和主应用都有自己的配置选项。 [参考文档](ref/options.md)
2. 修改页面， 默认的 view 太难看，我们要覆盖 override 改成自己的：
    `[工程文件夹]/view/DuckAdmin/[同名文件]` 
3. 获取提供对象
你可以在代码里得到管理员对象和 用户对象。 用于你的业务系统
如果得不到，将会抛出异常，你可以作后续处理。

```
$id = DuckAdminApp::AdminId();
$admin = DuckAdminApp::Admin(); // 获取当前 admin 对象，如果得不到 admin 对象，则会跳转登录
var_dump($admin);

```

4. 热修复，接管实现
假设我们对他哪个实现不满意。
```php
function onInit()
{
  $phase = DuckPhp::Phase(DuckAdminApp::class); //切入要修改的子应用的相位。
  UserAction::_(MyUserAction::_());   //修改单例。这里是你的 UserAction 由你实现。
  DuckPhp::Phase($phase);   //回到原相位
}
```

5. 使用全局函数,一般都在页面里使用。这些全局函数都是两个下划线开始的。

这是助手函数：

\_\_h 对应 CoreHelper::H(); HTML 编码

    function __l($str, $args = [])
\_\_l 对应 CoreHelper::L(); 语言处理函数，后面的关联数组替换 '{$key}'

    function __hl($str, $args = [])
\_\_hl 对应 CoreHelper::Hl(); 对语言处理后进行 HTML 编码

    function __json($data)
\_\_json 对应 CoreHelper::Json(); json 编码，用于向 javascript  传送数据

    function __url($url)
\_\_url 对应 CoreHelper::URL($url); 获得资源相对 url 地址

    function __res($url)
\_\__res 对应 CoreHelper::__res($url); 获取 外部资源地址

    function __domain($use_scheme = false)
\_\_domain 对应 CoreHelper::domain();  获得带协议头的域名

    function __display(...$args)
\_\_display 对应 `CoreHelper::Display()` 包含下一个 `$view` ， 如果 `$data = null` 则带入所有当前作用域的变量。 否则带入 `$data` 关联数组的内容。用于嵌套包含视图。



还有一批调试用的全局函数

    function __var_dump(...$args)
\_\_var_dump() 对应 CoreHelper::var_dump();  var_dump() 调试状态下 Dump 当前变量，替代 var_dump，和 var_dump 类似，实现可以修改

    function __trace_dump()
\_\_trace_dump() 对应 CoreHelper::TraceDump(); 调试状态下，查看当前堆栈，打印当前堆栈，类似 debug_print_backtrce(2)

    function __debug_log($str, $args = [])
\_\_debug_log() 对应 CoreHelper::DebugLog($message, array $context = array()) 对应调试状态下 Log 当前变量。

    function __is_debug()
\_\_is_debug() 对应 CoreHelper::IsDebug(); 判断是否在调试状态, 默认读取选项 is_debug 和设置字段里的 duckphp_is_debug

    function __platform()
\_\_platform() 对应 CoreHelper::Platform(); 获得当前所在平台,默认读取选项和设置字段里的 duckphp_platform，用于判断当前是哪台机器等

    function __is_real_debug()
\_\_is_real_debug() 对应 CoreHelper::IsRealDebug(); 切莫乱用。用于环境设置为其他。比如线上环境，但是还是要特殊调试的场合。 如果没被接管，和 IsDebug() 一致。
    
    function __logger()
\_\_logger() 对应 CoreHelper::Logger();  获得`Psr\Log\LoggerInterface`日志对象，便于不同级别的调试

    function __var_log($var)
\_\_var_log() 对应 CoreHelper::VarLog();  在日志打印当前变量 
    
    
所有 DuckPhp 的全局函数就这么讲完了 ^_^ 。 这些所有的全局函数，都有可接管的实现方法
这段内容和[全局函数参考](ref/Core-Functions.md) 差不多


致此，二次开发基本讲完了。要深入了解，那么我们就从自己搞个工程开始了

## 新工程
#### 建立新工程

我们进入新工程目录，然后用 composer 建立新的 DuckPhp 应用工程
```
composer require dvaknheo/duckphp # 用 require 
./vendor/bin/duckphp --help   # 查看有什么指令
./vendor/bin/duckphp new --namespace MyProject # 创建应用，命名空间为 MyProject
```
和大部分框架默认 app 作为应用命名空间不同， duckphp 的应用可以自定义自己命名空间。
而且你应该定义你自己的命名空间，而不是使用默认的命名空间。

### 快速开始一个页面

给工程文件添加`src/Controller/mytestController.php`内容如下

```php
<?php
namespace MyProject\Controller;
class mytestController
{
    public function action_i()
    {
        phpinfo();
        // Helper::Show(get_defined_vars(),null);
    }
}
```
然后我们执行内置的web 服务器
```
php duckphp-project run --port 8082
```
访问 `http://127.0.0.1:8082/mytest/i` （注意区分大小写）
看到 phpinfo 页面就说明成功了。后面 Business, Model 等的概念再说

### 文件结构

在这里，我们用 `tree -I 'public'`  列一下 新应用的文件结构。这里的参数是用于排除 `public` 目录上那些繁杂的例子。
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

小写的是资源文件夹，资源文件夹可以由 `$options['path']`设置为其他目录。
### 工程目录
* config 配置目录。通过修改应用的选项，也可以不需要这个目录
    * `config/DuckPhpSettings.config.php` 这个文件是存在的 ，只有根应用会有用，作用是保存设置的。
    * `config/DuckPhpApps.config.php` 这个是选项文件子应用的额外选项都在这里。安装的时候，会改写这个文件。
* runtime 目录是唯一需要可写的目录。默认情况下，日志会保存在这里
    * keepme.txt 只是 git 作用
* src 类文件夹。工程代码文件。后面详解
* view 视图目录
   * view/_sys/error404.php 404 错误展示页面
   * view/_sys/error404.php 500 错误展示页面
   * view/files.php 对应访问 `file` 的页面
   * view/main.php 对应访问 `` 的页面
   *view/test/done.php 对应访问 `test/done` 的页面
----

注意到我们排除了 public 目录,因为默认下带了很多示例文件
我们主要关心入口文件 index.php 他长的是这样子：

@script File: `template/public/index.php`
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
if (!class_exists(\MyProject\System\App::class)) {
    \DuckPhp\Core\AutoLoader::RunQuickly([]);
    \DuckPhp\Core\AutoLoader::addPsr4("MyProject\\", 'src'); 
}

$options = [
    // 这里可以添加更多选项
    //'is_debug' => true,
];
\MyProject\System\App::RunQuickly($options);
```
入口很简单，就是 Runqucikly ,把 选项数组带进去就是 。

`$options` 选项很复杂， 你的工程因为他们而不同。 有40多个。选项数组可以填什么，看配置[参考文档](ref/options.md)

然后，我们入口是 `\MyProject\System\App` 类，就是后面的内容
   
`\MyProject\System\App::RunQuickly($options); `
等价于
`\MyProject\System\App::_()->init($options)->run();`

`init()`初始化，然后 `run()` 运行。入口类只会被初始化一次，除非强制初始化。

### src 源代码目录

#### 引用到 `DuckPhp`系统的类
----

以下10个引用到 `DuckPhp`系统的类 除开这些类，你应该只在 System 里引用 DuckPhp 系统的类

4 个助手类对应 4 个 Trait

- MyProject\System\Helper => [DuckPhp\Helper\AppHelperTrait](ref/Helper-AppHelperTrait.md)
- MyProject\Controller\Helper =>  [DuckPhp\Helper\ControllerHelperTrait](ref/Helper-ControllerHelperTrait.md)
- MyProject\Business\Helper =>  [DuckPhp\Helper\BusinessHelperTrait](ref/Helper-BusinessHelperTrait.md)
- MyProject\Model\Helper => [DuckPhp\Helper\ModelHelperTrait](ref/Helper-ModelHelperTrait.md)


`Helper` 是各种助手方法,  `Helper`类都是和业务无关的类。通过这些Helper类的静态方法来调用 DuckPhp 系统的功能。 

所有 `Helper` 默认都只有静态方法,你自己的 Helper请用动态方法以表示区别。

这些助手类都实现了 `_()` 可变单例方法

如果你的系统足够小，你也可以把这些 HelperTrait 内嵌入相应的基类或类里。

3 个基类 Trait

- MyProject\Controller\Base => [DuckPhp\Foundation\SimpleControllerTrait](ref/Foundation-SimpleControllerTrait.md)
- MyProject\Business\Base => [DuckPhp\Foundation\SimpleBusinessTrait](ref/Foundation-SimpleBusinessTrait.md)
- MyProject\Model\Base => [DuckPhp\Foundation\SimpleModelTrait](ref/Foundation-SimpleModelTrait.md)

这些基类都实现了 `_()` 可变单例方法。 而且都有 `CallInPhase($phase)`静态方法以便跨相位调用

3 个功能性Trait

- Project\System\ProjectExcepiton => [DuckPhp\Foundation\SimpleExceptionTrait](ref/Foundation-SimpleExceptionTrait.md)
- Project\Controller\Session [DuckPhp\Foundation\SimpleSessionTrait](ref/Foundation-SimpleSessionTrait.md)
- Project\Controller\ExceptionReporter => [DuckPhp\Foundation\ExceptionReporterTrait](ref/Foundation-ExceptionReporterTrait.md)

`ProjectException::ThrowOn($flag,$message,$code = 0);` 如果`$flag` 为真则抛奔异常。

`Session` ， session 容器，bean, 扩展规范 `get` `set` 方法

#### System
----
@script File: `template/System/App.php`
```php
class App extends DuckPhp
{
    //@override
    public $options = [
        //'is_debug' => true, // debug switch
        //'path_info_compact_enable' => false,
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        
        'exception_reporter' => ExceptionReporter::class,
    ];
    protected function onInit()
    {
        //
    }
    /**
     * console command sample
     */
    public function command_hello()
    {
        echo "hello\n";
    }
    
}
```
 * App.php 是重载 DuckPhp 类的 入口位置

你可以看到这里也有个 `$options` ，这里的 `$options` 和 `RunQuickly` 的 `options` 合并一起。以后者优先。

被注释的 `is_debug`选项，是调试选项，你可以开启这个选项以开启调试模式

被注释的 `path_info_compact_enable` 用于没开启 nginx 配置的时候，兼容无 `path_info` 模式

`'error_404' => '_sys/error_404',`  404错误页面

`'error_500' => '_sys/error_500',`  500错误页面

有一项 特殊选项 `'exception_reporter' => ExceptionReporter::class`, 这是把错误处理重定向到控制器的 `ExceptionReporter`类处理


`'ext'=> [] ,`  默认没加载其他扩展，你可以把其他应用作为子应用加在这里，就像前面示例中那样。

`protected function onInit()` 在 `init()` 最后阶段会调用，你可以再次调整你的工程代码

`command_hello()` 这是命令行下 `duckphp-project hello` 的入口。具体详见   [Console](ref/Core-Console.md) 的文档

System 目录是 **业务工程师** 不需要修改的，修改这的东西，都是 **核心工程师** 来修改

App 的 run 方法，就根据路由，执行 Controller 目录下相关的类

#### Controller

----

Web的入口就是控制器， DuckPhp 理念里，Controller 只处理web入口。 业务层由 Business 层处理。



----

`MyProject\Controller\MainController  主入口类

`action_`  对应选项 `$options['controller_method_prefix']='action_'` 。 

//PathInfo

`MyProject\Controller\testController`

`-Controller` 的之间不要相互调用， 我们把完成部分逻辑的控制器，可以放到 `-Action` 结尾的类里


控制器里不要写业务，做的是输入和输出的处理。 业务层负责功能。调用业务层，而不是模型层

用`Helper::Show()` 来显示数据

----

以下深入固定类的讲解

`MyProject\Controller\Base` 控制器基类，实现了 'CallInPhase()'固定方法，以及修改了`_()`可变单例的实现，使得可以方便的替换控制器类。

`MyProject\Controller\ControllerException`，控制器层的异常类，继承 `ProjectException` 拥有 `ThrowOn` 固定方法，用法见前面。

`MyProject\Controller\Session`， 处理会话。

`MyProject\Controller\ExceptionReporter` 则是处理各种异常的入口。

`MyProject\Controller\Helper` 有很多静态方法。也是 **业务工程师** 需要熟练掌握的 具体参见 [DuckPhp\Helper\ControllerHelperTrait](ref/Helper/ControllerHelperTrait) 文档。
分类有：

1. 超全局变量

`GET/POST/REQUEST/COOKIE/SERVER($key, $default = null)`

替代同名 `$_GET / $_POST / $_REQUEST / $_COOKIE/ $_SERVER` 。如果没值返回后面的 $default 默认值。如果 $key 为 null 返回整个数组。

2. 显示处理

`Show($data = [], $view = '')` `Render($view, $data = null)`  `setViewHeadFoot($head_file = null, $foot_file = null)` `assignViewData($key, $value = null)`

`Show` 是经常用的函数，用于显示页面，`setViewHeadFoot` 设置页眉页脚。 `assignViewData用`于向页面填充数据。

`Render` 渲染得出字符串， 注意的是，调用参数正好和 `Show` 相反，因为 `Show`() 数据在前面更方便。 

3. 配置
    public static function Setting($key)
    public static function Config($file_basename, $key = null, $default = null)

4. 跳转`
`ExitRedirect/ExitRedirectOutside/ExitRouteTo/Exit404/ExitJson($url, $exit = true)`

跳转方法的 $exit 为 true 则附加 exit()

相应的是站内跳转，站外跳转，应用内跳转，404跳转， json跳转

5. 路由相关
`getRouteCallingClass() getRouteCallingMethod()` 获得正在调用的类 和获得正在调用的方法。

`PathInfo() Domain() Parameter()` ,额外信息
    
6. 系统兼容替换

和系统同名函数(header/setcookie/exit)功能一致，目的是为了兼容不同平台比如 php-fpm 和 php-cli 下

7. 分页相关
`PageNo($new_value = null) PageWindow($new_value = null) PageHtml($total, $options = [])`
设置分页当前页码，设置当前分页页面宽度，（不用 PageSize 是有点小原因） 获得 分页字符串，后面选项见 [分页类参考](ref/Component-Pager.md)

8. 异常处理

DuckPhp 的异常处理 可以参见 待定文档说明。

    public static function assignExceptionHandler($classes, $callback = null)
分配异常类回调

    public static function setMultiExceptionHandler(array $classes, $callback)
给多个异常类都帮定到一个回调处理

    public static function setDefaultExceptionHandler($callback)
设置默认的异常处理

    public static function ThrowByFlag($exception, $flag, $message, $code = 0)
给没`ThrowOn`的异常，添加一个快捷

    public static function XpCall($callback, ...$args)
回调，如果正常返回没事，如果抛异常则返回异常。

9. 事件处理
`FireEvent($event, ...$args)` `OnEvent($event, $callback)`
触发一个事件， 设置事件回调， DuckPhp 的事件系统是一对多，后到先得得。

10. 相关对象

    public static function Admin()
    public static function AdminId()
    public static function User()
    public static function UserId()
这段代码将会调整, 得到管理员对象或者用户对象


#### Business 
----
作为程序员专家，大家达成的意见是 业务逻辑层要抽出来，业务逻辑 英文是什么 Business Logic 嘛。

有人用Logic ，这里我用的是 Business 命名 还有人用 Service。

需要注意的是，虽然有人把这层独立出来，但是代码里却是和 web 相关， Business 要求是什么，和Controller 无关，无状态。可测。

当然，有些人会带上用户 ID ，这种一两个的例外。

相比 Model 目录，这里多了 BusinessException 。 因为规范要求 model 类不得抛异常

`MyProject\Business\BusinessException` 默认异常类，继承 ProjectException。
----


`MyProject\Business\DemoBusiness` 示例业务类

`MyProject\Business\CommonService`

`-Business` 的之间不要相互调用， 我们把完成部分逻辑的控制器，可以放到 `-Service` 结尾的类里


这就是 Business 层不用 Service 来命名的原因 

----
以下是固定的类

`MyProject\Business\Base`  业务基类很简单，就实现了 `_()` 可变单例方法。 以及 `CallInPhase()`方法

`MyProject\Business\Helper` 。三个配置相关方法，两个事件方法，和两个其他方法。

    public static function Setting($key)
    public static function Config($key =null , $default = null $file_basename = 'config')
    public static function FireEvent($event, ...$args)
    public static function OnEvent($event, $callback)
    public static function XpCall($callback, ...$args)
    public static function ThrowByFlag($exception, $flag, $message, $code = 0)
以上见 `MyProject\Controller\Helper` 的说明。

    public static function Cache($object = null)
获得缓存对象 `BusinessHelperTrait` 仅 `ControllerHelperTrait` 多了 `Cache()`方法

#### Model


`DemoModel.php` 这是示例 Demo ，命名是按数据库表名来

`CrossModelEx.php` 这是示例 跨表 Demo ，你可以删除他重建

不推荐 Model 层里抛异常，所以 Model 层没有 ModelException

----

固定的类

`MyProject\Model\Helper`

方法只有下面五个

    public static function Db($tag = null)
    public static function DbForRead()
    public static function DbForWrite()

参见 [DuckPhp\Component\DbManager::DbForWrite](Component-DbManager.md#DbForWrite)

    public static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string
分页 limit 的 sql,补齐 sql用

    public static function SqlForCountSimply(string $sql): string
简单的把 `select ... from ` 替换成 `select count(*)as c from ` 用于分页处理。


`MyProject\Modl\Base` 

DuckPhp 的 Model 层是很传统的跟着数据库表名走的模式。

    public function table()
获取表名，你的 Model 类可以重写这个方法

    public function prepare($sql)
预处理sql 语句，把 `'TABLE'`改为表名

    protected function fetchAll($sql, ...$args)
    protected function fetch($sql, ...$args)
    protected function fetchColumn($sql, ...$args)
    protected function fetchObject($sql, ...$args)
    protected function fetchObjectAll($sql, ...$args)
预处理后，从数据的从库里，根据 sql 获取相关数据。

    protected function execute($sql, ...$args)
预处理后，执行 sql 语言

    protected function update($id, $data)
    protected function add($data)
    protected function find($a)
    protected function getList(int $page = 1, int $page_size = 10)
内置快速方法。

----

## 其他参考文档

这些文档是 **核心工程师** 才需要熟读的内容，列举如下：


### 理解相位
1.2.13 版本，我们为每个子应用做了相位隔离。不同子应用
所以 DuckAdmin 的 Route::_() 就和 DuckUser 的 Route::_() 是不同实例了。
相位是以主类作为 命名空间隔离的。
切入相位， 共享相位的单位， 内容的 dump
### 调用

DuckAdmin\System\UserApi::CallInPhase($phase)->foo();
Admin 和 User 这两个特殊
MyApp::AdminId();
MyApp::Admin()->data();
MyApp::User()->data();
### 替换
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

## FAQ

Q: _()方法是不是糟糕了

你可以把 ::_()-> 看成和 facades 类似的门面方法。
可变单例是 DuckPhp 的核心。
你如果引入第三方包的时候，不满意默认实现，可以通过可变单例来替换他

var_dump(MyClass::__()); 使用 Facades 就没法做到这个功能。

Q: 为什么不直接用 Db 类，而是用 DbManager

A: 做日志之类的处理用

Q: 为什么名字要以 *Model *Business *Controller 结尾
让单词独一无二，便于搜索

Q: 为什么是 Db 而不是 DB 。
A: 为了统一起来。  缩写都驼峰而非全大写

Q: 回调 Class::Method Class@Method Class->Method 的区别

A:
-> 表示 new 一个实例
@ 表示 $class::_()->

:: 表示 Class::Method

~ => 扩充到当前命名空间
