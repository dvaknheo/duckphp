# DNMVCS 介绍
## DNMVCS 是什么
一个 PHP Web 简单框架 
* 主要特点：比通常的 Model Controller View 多了 Service 。拟补了 常见 Web 框架少的缺层。
这个缺层导致了很糟糕的境地。你会发现很多人在 Contorller 里写一堆代码，或者在 Model 里写一堆代码。
使得网站开发者专注于业务逻辑。

* 为偷懒者写的。只需要引用一个文件，不做一大堆外部依赖。composer 安装正在学习中。
* 替代 Codeiginter 这个PHP4 时代的框架，只限于新工程。
* 不仅仅支持全站路由，还支持局部路径路由和非 PATH_INFO 路由,不需要配服务器也能用
* 耦合松散，扩展灵活方便，魔改容易
* 小就是性能。（不过也上千行代码了）
* 无第三方依赖，你不必担心第三方依赖改动而大费周折。
*
* 和 Swoole 整合实现高性能 web 服务器。
## 关于 Servivce 层
MVC 结构的时候，你们业务逻辑放在哪里？
新手放在 Controller ，后来的放到 Model ，后来觉得 Model 和数据库混一起太乱， 搞个 DAO 层吧。
可是 一般的 PHP 框架不提供这个功能。
所以，Service 按业务走，Model 层按数据库走，这就是 DNMVCS 的理念。
DNMVCS 的最大意义是思想，只要思想在，什么框架你都可以用
你可以不用 DNMVCS 实现 Controller-Service-Model 架构。
只要有这个思想就是理念成功了。
## 理解 DNMVCS 的原则
DNMVCS 层级关系图

```
           /-> View
Controller --> Service ---------------------------------> Model   
                      \               \              /
                       \-> LibService --> ExModel --/
```
* Controller 按 URL 入口走 调用 view 和service
* Service 按业务走 ,调用 model 和其他第三方代码。
* Model 按数据库表走，基本上只实现和当前表相关的操作。
* View 按页面走
* 不建议 Model 抛异常
1. 如果 Service 相互调用怎么办?
添加后缀为 LibService 用于 Service 共享调用，不对外，如MyLibService
2. 如果跨表怎么办?，三种解决方案
    1. 在主表里附加，其他表估计用不到的情况。
    2. 添加后缀为 ExModel 用于表示这个 Model 是多个表的，如 UserExModel。
    3. 或者单独和数据库不一致如取名 UserAndPlayerRelationModel

## DNMVCS 做了什么
* 简单可扩展灵活的路由方式
    * 全站 PATH_INFO 模式
    * 局部 PATH_INFO 模式
    * GET 参数的路由模式
    * 路由表的路由模式
* 简单可扩充的数据库管理类
    * 支持主从(手动)
    * 可扩充
    * 轻松整合 Medoo
* 简单的视图
    * PHP 本身就是模版
    * 轻松处理页眉页脚
* 扩展接管默认错误处理
* 简单的配置类
    * setting 就是一个数组， config 就是动态配置
* 简单的加载类 
* 所有这些仅仅是在主类里耦合。
* **支持Swoole** 不需要改动代码就能在swoole 里运行
## DNMVCS 不做什么
* ORM ，和各种屏蔽 sql 的行为，根据日志查 sql 方便多了。 自己简单封装了 pdo 。你也可以使用自己的DB类。
* 模板引擎，PHP本身就是模板引擎。
* Widget ， 和 MVC 分离违背。
* 接管替代默认的POST，GET，SESSION 。系统提供给你就用，不要折腾这些。 *为支持 swoole 你需要改动*

## DNMVCS 还要做什么

* composer 安装模式，本人还没学会
* 范例，例子还太简单了

## DNMVCS 的 缺点

1. 不优雅。万恶之源。 
2. 调用堆栈层级太少，不够 Java 。
3. 虽然实现了标准的 PSR-4 规范实现，但是还给懒鬼们开了后门。
4. 错误报告页面很丑陋。 想华丽自己写一个。不用 IDE 的直接看就懂。
5. 没有中间件。 重写 Controller 啊，要什么中间件。
6. 没有强大的全局依赖注入容器，只有万能的 G 函数。
7. 没有灵活强大的 AOP ，只有万能的 G 函数。
8. 这框架什么都没做啊。 居然只支持 PHP7 。

## 还有什么要说的

使用它，鼓励我，让我有写下去的动力

# DNMVCS 使用手册
## 开始
### 第一步
跑起来。
1. 下载 DNMVCS。
2. 把 web 目录设置为 sample/public 目录。
3. 复制 config/setting.sample.php 为 config/setting.php
4. 浏览器中打开主页出现下面的欢迎就表示基本成功

```
Hello DNMVCS

Time Now is 2018-06-14T22:16:38+08:00
```
如果漏了修改 config/setting.php 会提示：
```
DNMVCS Fatal: no setting file!,change setting.sample.php to setting.php !
```
*DNMVCS并非一定要外置设置文件，有选项可改为使用内置设置选项。*
### 后续的工作
新建工程怎么做？ 复制 sample 目录到你工程目录就行，修改 index.php ，使得引入正确的库

还有哪些没检查的？ 服务器配置 PATH_INFO 对了没有。 数据库也没配置和检查。

想要更多东西，可以检出  dnmvcs-full 这个工程，里面有全部的测试样例。 *尚未完成*

开始学习吧！

### 目录结构

工程的目录结构
```
+---app // psr-4 标准的自动加载目录
|   +---Base   // 基类放在这里
|   |      App.php    // 默认框架入口文件
|   +---Controller  // 路由控制器
|   |       Main.php    // 默认控制器入口文件
|   +---Model       // 模型放在里
|   |       TestModel.php   // 测试 Model 
|   \---Service     // 服务放在这里
|           TestService.php //测试 Service
+---classes         //自动加载的类，放在这里(建议)
|       ForAutoLoad.php // 测试自动加载
+---config          // 配置文件 放这里
|       config.php  // 配置，目前是空数组
|       setting.php // 设置，敏感文件，不放在版本管理里
|       setting.sample.php  // 设置，对比来
+---lib             // 手动加载的文件放这里
|       ForImport.php //用于测试导入文件
+---view            // 视图文件放这里
|   |   main.php  // 视图文件
|   \---_sys        // 系统错误视图文件放这里
|           error-404.php  // 404 
|           error-500.php  // 500 出错了
|           error-debug.php // 调试的时候显示的视图
|           error-exception.php // 出异常了，和 500 不同是 这里是未处理的异常。
\---public             //  网站目录放这里
        index.php // 主页面
```
解读

public/index.php  入口 PHP 文件,内容如下
```php
<?php
require('../../DNMVCS/DNMVCS.php');
//$path=realpath('../');
$options=[
];
\DNMVCS\DNMVCS::RunQuickly($options);
//\DNMVCS\DNMVCS::G()->init($options)->run();
```
被注释掉部分 和 实际调用部分实际相同。是个链式调用。
DNMVCS\DNMVCS::G(); 单例模式。 
DNMVCS\DNMVCS 主类，在后面有好多其他方法详细介绍。
这些方法背后是不同的你可以改写的类。

init([]);初始化，这部分入口选项见后面章节详细介绍
run(); 开始路由

### 深入的级别

1. 使用默认选项实现目的 
2. 只改配置实现目的
3. 继承接管特定类实现目的
4. 魔改。
## 单文件模式
```php
\DNMVCS\DNMVCS::RunOneFileMode([]);
```
不想依赖这么多，一个文件解决？可以。
### 不用 path_info 的模式

```php
\DNMVCS\DNMVCS::RunWithoutPathInfo([]);
```

## 选项
    init($options) 方法的参数是可配置的，默认设置是分三个类别的组合。
    多余的缩进里的选项是不建议修改的。
    下面是默认的配置详解

```php
const DNAutoLoader::DEFAULT_OPTIONS=[
    'namespace'=>'MY',                  // 默认的命名空间，你可以自定义工程的命名空间
        'path_namespace'=>'app', 	    // 命名空间根目录
    'with_no_namespace_mode'=>true,     // 简单模式，无命名空间直接 controller, service,model
        'path_no_namespace_mode'=>'app', // 简单模式的基本目录
];
```
autoload 自动加载相关的选项

```php
const DNMVCS::DEFAULT_OPTIONS=[
    'base_class'=>'MY\Base\App',        // override 重写 系统入口类代替 DNMVCS 类。
        'path_view'=>'view',            // 视图目录，或许会有人改到 app/View
        'path_lib'=>'lib',              // 用于手动导入 DNMVCS::Import() 的类的目录
    'setting'=>[],        				// 设置，设置文件里填写的将会覆盖这一选项
    'all_config'=>[],        			// 配置，每个配置用 key  分割。
        'setting_file_basename'=>'setting',        // 设置的文件名，如果为'' 则不读取设置文件
    'is_dev'=>false,					//是否在开发状态，设置文件里填写的将会覆盖这一选项
    'db_create_handler' =>'',			// 创建DB 的回调 默认用 DNDB::class
    'db_close_handler' =>'', 			// 关闭DB 类的回调。
    'ext'=>[],                          //默认不使用扩展
    'rewrite_list'=>[],
    'route_list'=>[],
    'swoole'=>[],               // swoole_mode 模式，和 superGlobal 整合


        'error_404'=>'_sys/error-404',      // 404 错误处理，传入字符串表示用的 view,如果传入 callable 则用 callback,view 优先
        'error_500'=>'_sys/error-500',      // 500 代码有语法错误等的页面，和 404 的内容一样
        'error_exception'=>'_sys/error-exception',  // 默认的异常处理。和前面类似
        'error_debug'=>'_sys/error_debug',  // 调试模式下出错的处理。和前面类似
];
```
    关于 base_class 选项。
    你可以写 DNMVCS 的子类 用这个子类来替换DNMVCS 的入口。详情见后面。
    ext 会加载 DNMVCSExt 实现一些扩展性的功能。后面章节会说明。

```php
const DNRoute::DEFAULT_OPTIONS=[
    'with_no_namespace_mode'=>true,     // 简单模式，无命名空间直接 controller, service,model
    'prefix_no_namespace_mode'=>''      // 无命名空间模式时候的类名前缀
    'enable_paramters'=>false,          // 支持切片模式
    'enable_post_prefix'=>true,         // 把 POST 的 映射为 do_$action 方法
        'path_controller'=>'app/Controller',    //controller 的目录
        'namespace_controller'=>'MY\Controller',   //controller 的命名空间 MY\Controller
        'default_controller_class'=>'DNController', //默认 controller 名字为 DNController
        'disable_default_class_outside'=>false, // 屏蔽  Main/index  第二访问模式
];
```

    这段是和路由相关的。namespace 和 with_no_namespace_mode 选项也会影响路由。
    enable_paramters 切片模式。 使得 foo->a() 也支持 foo/a/b/c 这样的路由，而不是 404。
    enable_post_prefix 默认把 POST 的方法映射到 do_$action 这样处理起来方便些。
    default_controller_class 可以设置为空

### 设置文件
    默认情况下会读取 ::/config/setting.php 里的设置。
    你可以用过 setting_file_basename='' 使得不读取这里的设置
    工程的设置文件样例 setting.sample.php 。选项很少

```php
<?php
// copy me to "setting.php"
return [
    'is_dev'=>true,
    'db'=>[
        'dsn'=>'mysql:host=???;port=???;dbname=???;charset=utf8;',
        'username'=>'???',
        'password'=>'???',
    ],
    'db_r'=>null,
];
```
    关于 is_dev ，这个标记用于判断是否在开发状态，影响 DNMVCS::G()->isDev();
    db，配置数据库。
    关于 db_r， 配置读写分离的数据库。
    默认配置为空，这使得 DNMVCS::DB_R() 和 DNMVCS::DB 的函数表现一致。都是从主数据库里读的。

### 选项，设置，配置的区别
    
* options 选项，代码里的设置
* setting 设置，敏感信息
* config 配置，非敏感信息

## 开始自己的代码
以 /about/foo 为例，使用无命名空间模式，这省掉一些代码

首先我们要写相关控制器

::app/Controller/about.php
```php
<?php
class DNController
{
    public function foo()
    {
        echo MiscService::G()->foo();
    }
}
```
非 swoole 模式下，控制器可以不用和路由一样的名称，用默认的 DNController

在控制器里，我们调用了 MiscService 这个服务。
MiscService 调用 MiscModel 的实现。此外，我们要调整 返回值的内容
我们用 DNSingleton单例。

::app/Service/MiscService.php
```php
<?php
class MiscService
{
    use \DNMVCS\DNSingleton;
    public function foo()
    {
        //TODO log something.
        $time=MiscModel::G()->getTime();
        $ret='Now is'.$time;
        return $ret;
    }
}
```
完成 MiscModel
Model 类是实现基本功能的

::app/Model/MiscModel.php
```php
<?php
class MiscService
{
    use \DNMVCS\DNSingleton;
    public function getTime()
    {
        return DATE(DATE_ATOM);
    }
}
```
附加，在初始化里我们要做其他事情。
根据 base_class 选项，我们有。

::app/Base/App.php 
```php
<?php
namespace MY\Base;
class App extends \DNMVCS\DNMVCS
{
    public function init($options=array())
    {
        // switch  me , DNView::G(MYView::G());
        parent::init($options);
        // switch  me , $this->initView(DNView::G(MYView::G());
        return $this;
    }
}
```
这就是 DNMVCS 下的简单流程了。其他开发类似。

## 理解路由和控制器
DNMVCS 的控制器有点像CodeInigter，不需要继承什么，就这么简单。
1. 按名字切分

    甚至连名字都不用，用默认的 DNController 就够了。
    而且支持子命名空间多级目录。如果开启简单模式，也可用 __ 双下划线代替 \ 切分。
2. 处理同名
    Swoole 兼容不可以用这种偷懒模式)
    DNController 重名了怎么办，比如我要相互引用？ 
    1. 那是你不应该这么做，
    2. 你也可以采取名称对应的类，而不偷懒啊啊。

3. DNMVCS 还支持路由映射。 

    正则用 ~
    要指定 GET/POST 在最前面加http 方法.

    DN::G()->assignRoute('GET ~article/(\d+)','article->get');

    *用->表示类调用而不是静态调用*
    DNMVCS 支持 Paramter，你可以在设置里关掉。

    Parameter 切片会直接传递进 方法里作为参数。

    路由表里，用正则切分会传递进方法，不管是否开启 enable_paramters
    
4. 不用 PATH_INFO
    比如 路由不用 path_info 用 $_GET['_r'] 等，很简单的。
    simple_route_key 开启 _GET 模式路由
    如果你想加其他功能，可以 添加钩子， 继承 DNRoute 自行扩展类。  两种方式灵活扩展

run() 方法开始使用路由。 如果你不想要路由。只想要特定结构的目录， 不调用 run 就可以了。
比如只想要 db 类等等。


## 重写 错误页面

错误页面在 ::view/_sys/ 目录下 里。你可以修改相应的错误页面方法。
比如 404 是 view/404.php 。
高级一点，你可以 扩展 DNMVCS 的主类实现。

DNMVCS 的报错页面还是很丑陋，需要调整一下
无错误页面模式，会自己显示默认错误
# DNMVCS 主类
## 基本方法
```
static G($object=null,$args=[])   
    G 单例函数是整个系统最有趣的地方。
    传入 $object 将替代默认的单例。
    比 PHP-DI简洁，后面的文档 会有详细介绍

init($options=[])
    初始化，这是最经常子类化完成自己功能的方法。
    你可以扩展这个类，添加工程里的其他初始化。
run()
    开始路由，执行。这个方法拆分出来是为了，不想要路由，只是为了加载一些类的需求的。
    如果404 则返回false;其他返回 true
static RunQuickly($options=[])
    DNMVCS::RunQuickly ($options) 相当于 DNMVCS::G()->init($options)->run();
static RunOneFileMode($optionss=[])
    单一文件模式，不需要其他文件，设置内容请放在
    $options['setting'] 里
static RunWithoutPathInfo()
    不需要 PathInfo 的模式。用 _r 来表示 Path_Info
static DI($name,$object=null)
    你们想要的 container。如果 $object 不为null 是写，否则是读。
```
## 常用静态方法方法
这些方法因为太常用，所以静态化了。
包括 视图view,路由，数据库，配置 ，

Show($data=[]],$view=null)

    显示视图 
    视图的文件在 ::view 目录底下.
    为什么数据放前面，DN::Show(get_defined_vars());把 controller 的变量都整合进来，并用默认路径作为 view 文件。
    实质调用 DNView::G()->_Show();
DB()

    数据库
    数据库管理类 DNManager 里配置的数据库类
    实质调用 DBManager::G()->_DB();
DB_W()

    返回写入的数据 
    默认和 DB() 函数一样
    实质调用 DBManager::G()->_DB_W();
DB_R()

    读取用的数据库
    实质调用 DBManager::G()->_DB_R();
URL($url)

    获得调整路由后的url地址 
    当你重写 DNRoute 类后，你可能需要重写这个方法来展示
    比如 simple_route_key 开启后， URL('class/method?foo=bar') 
    将会是 ?r=class/method&foo=bar ，而不是 /class/method?foo=bar
    如果是 / 开始的 URL ，将是从网站根目录开始。

    实质调用 DNRoute::G()->_URL();
Parameters()

    获得路径切片 
    当用正则匹配路由的时候，匹配结果放在这里。
    如果开启了 eanbale_parameter 匹配选项也会在这里。
    这会使得 /about/foo/123/456 路由调用方法为 => about->foo(123,456)
    实质调用 DNRoute::G()->_Parameters();
Setting($key)

    读取设置
    设置在 ::/config/setting.php 里，php 格式
    配置非敏感信息，放在版本管理中，设置是敏感信息，不保存在版本管理中
    实质调用 DNConfig::G()->_Setting();
Config($key,$file_basename='config')

    读取配置 
    配置放在 config/$file_basename.php 里，php 格式
    配置是放在非敏感信息，放在版本管理中
    实质调用 DNConfig::G()->_Config();
LoadConfig($file_basename)

    加载其他配置
    如果很多配置文件，手动加载其他配置
    实质调用 DNConfig::G()->LoadConfig();
ExitJson($ret)

    打印 json_encode($ret) 并且退出。
    这里的 json 为人眼友好模式。

    实质调用 DNView::G()->_ExitJson();
ExitRedirect($url)

    跳转到另一个url 并且退出。
    实质调用 DNView::G()->_ExitRedirect();
ExitRouteTo($url)

    跳转到 URL()函数包裹的 url。
    应用到 DNView::G()->ExitRedirect(); 和 DNRoute::G()->URL();
    高级开发者注意，这是静态方法里处理的，子类化需要注意
ThrowOn(\$flag,\$message,\$code);

    如果 flag 成立则抛出 DNException 异常。
    减少代码量。如果没这个函数，你要写
    if($flag){throw new DNException($message,$code);}
    折腾
    如果是你自己的异常类 ，可以 use DNThrowQuickly 实现 ThrowOn 静态方法。
Import($file)

    手动导入默认lib 目录下的包含文件
    实质调用 self::G()->_Import();
    
## 独立杂项静态方法
这几个方法独立，为了方便操作，放在这里。

H(&$str)

    html 编码 这个函数常用，所以缩写。H 函数还支持 数组

RecordsetH(&$data,$cols=[])

    给 sql 查询返回数组 html 编码
    $cols 指定 要转码的列名

RecordsetURL(&$data,$cols_map=[]) 

    给 sql 返回数组 加url 比如  url_edit=>"edit/{id}",则该行添加 url_edit =>DN::URL("edit/".$data[]['id']) 等类似。

## 非静态方法
这里的方法偶尔会用到，所以没静态化 。
assign 系列函数，都有两个模式 func(\$map)，和 func(\$key,\$value) 模式方便大量导入。

isDev()

    判断是否在开发状态。默认读设置里的 is_dev ，

assignRoute($route,$callback=null)

    给路由加回调。
    关于回调模式的路由。详细情况看之前介绍
    和在 options['route'] 添加数据一样
    assigenRoute 之后，会引用 DNRouteHookAdvance.php
assignRewrite($old_url,$new_url=null)

    rewrite  重写 path_info
    不区分 request method , 重写后可以用 ? query 参数
    ~ 开始表示是正则 ,为了简单用 / 代替普通正则的 \/
    替换的url ，用 $1 $2 表示参数

    assignRewrite 之后，会引用 DNRouteHookAdvance.php
getRouteCallingMethod()

    获得路由中正在调用的方法。
    用于控制器里判断方法以便于权限管理。
    也适用于重写URL后判断是否是直接访问

    实质调用 DNRoute 的 getRouteCallingMethod
setViewWrapper($head_file=null,$foot_file=null)

    给输出 view 加页眉页脚 
    view 里的变量和页眉页脚的域是一样的。
    页眉页脚的变量和 view 页面是同域的。
    有时候你需要 setViewWrapper(null,null) 清理页眉页脚

    实质调用 DNView::G()->setViewWrapper
assignViewData($key,$value=null)

    给 view 分配数据，实质调用 DNView::G()->assignViewData
    这函数用于控制器构造函数添加统一视图数据
showBlock($view,$data)

    展示一块 view ，用于调试的场合。
    展示view不理会页眉页脚，也不做展示的后处理，如关闭数据库。
    注意这里是 $view 在前面， $data 在后面，和 show 函数不一致哦。
    实质调用 DNView::G()->showBlock
assignExceptionHandle($classes,$callback=null)

    分配特定异常回调。
    用于控制器里控制特定错误类型。 
    // TODO 优化 多个 classes  名称共享一个
addRouteHook($hook,$prepend=false)

    下钩子扩展 route 方法
    实质调用 DNRoute 的 addRouteHook
setDefaultExceptionHandle($calllback)

    接管默认的异常处理，所有异常都归回调管，而不是显示 500 页面。
    用于控制器里控制特定错误类型。比如 api 调用
assignPathNamespace($path,$namespace=null)
    
    分配自动加载的命名空间的目录。
## 事件方法
实现了默认事件回调的方法。扩展以展现不同事件的显示。

```
onBeforeShow()
    在输出 view 开始前处理.
    默认处理空模板为当前类和方法，默认关闭数据库。
    因为如果请求时间很长，页面数据量很大。没关闭数据库会导致连接被占用。
onShow404()
    404 回调。这里没传各种参数，需要的时候从外部获取。
onException($ex)
    发生未处理异常的处理函数。显示 exception 页面
onErrorException($ex)
    处理错误，显示500错误。
onDebugError($errno, $errstr, $errfile, $errline)
    处理 Notice 错误。
onErrorHandle($errno, $errstr, $errfile, $errline)
    处理 PHP 错误
```
## 组件初始化
初始化组件，供扩展组件时初始化用。

initConfiger(DNConfiger $configer)

    初始化配置。
    配置路径。
    设置是否是开发状态
initView(DNView $view)

    初始化视图。 做了两件事
    配置路径
    绑定 onBeforeshow
initRoute(DNRoute $route)
    初始化路由 配置选项。
    绑定 onShow404
initDBManager(DNDBManger $dbm)
    初始化数据库管理器
    db_create_handler ，db_close_handler 用在这里。
    db_create_handler($config,$tag)
    db_close_handler($db,$tag)
initMisc
    如果 swoole_mode 启用  use_super_global
    如果 ext 启用 AppExt

## 其他方法

高级方法是一般不会用到的方法。

setBeforeRunHandler($before_run_handler)

    在run之前执行回调。 SwooleHttpServer 用到这个。
# 进一步扩展
## 总说
DNMVCS 系统 是用各自独立的类合起来的。
DNMVCS 主类，单向调用这几个组件，各组件是独立的。
例外是单例模式和抛异常的时候都会用到 
```
DNMVCS->init
    DNAutoloader
    init DNConfiger

DNMVCS->run
    DNRoute  -> RouteHook::hook();
    
    DNView
    DNDBManager -> DNDB::CreateDBInstence(),DNDB::CloseDBInstence()
    DNExceptionManager
```
DNMVCS 主类里一些函数，是调用其他类的实现。基本都可以用 G 方法替换

DNMVCS 的各子类都是独立的。现实中应该不会拿出来单用吧

DNDBManger 调用 DNDB 类，用于管理数据库

## trait DNSingleton | 子类化和 G 方法
**很重要的一节**
```php
<?php
trait DNSingleton
    public static function G($object=null):object
```
G 函数，单例模式。

如果没有这个 G 方法 你可能会怎么写代码：
```
(new MyClass())->foo();
```
绑定 DNSingleton 后，这么写
```
MyClass::G()->foo();
```
另一个隐藏功能：
```
MyBaseClass::G(new MyClass())->foo();
```
MyClass 把 MyBaseClass 的 foo 方法替换了。
接下来后面这样的代码，也是调用 MyClass 的 foo2.
```
MyBaseClass::G()->foo2();
```
**注意但是静态方法不替换，请注意这一点。**

为什么不是 GetInstance ? 因为太长，这个方法太经常用。

所以你可以扩展各种内部类以实现不同功能。

比如你要自己的路由方法.在 init 里。
```php
//MYMVCS::init
public function init($options=[])
{
    DNRoute::G(MYRoute::G());
    parent::init($options);
}
```
这样 MYRoute 就接管了 DNRoute 了。


DNView::G(AdminView::G());
这样 AdminView 就接管了 DNView 了。

G 函数的缺点：IDE 上没法做类型提示，这对一些人来说很重要。

service , model 上 用  static 函数代替 G 函数实例方式或许也是一种办法


组件在后续使用，记得初始化：

DNMVCS 一共有 4个组件初始化。

你不需要 override 这些组件初始化函数，你需要在相应的初始化函数里调用这些方方初始化就是

*因为 autoloader 不建议替换，所以没有 initAutoloader();*

下面就是个初始化 route 和 view 的例子。
```php
<?php
namespace MY\Base;
class App extends \DNMVCS\DNMVCS
{
    public function init($options=array())
    {
        \DNMVCS\DNRoute::G(Route::G());
        parent::init($options);
        $this->initView(\DNMVCS\DNView::G(View::G()));
        return $this;
    }
}
```
## 更高级的 G 函数

final class DNSingletonStaticClass

DNSwooleApp 用到了这个，使得在协程的里的和协程外的单例不是同一个。

DNSimpleSinglton DNAppExt.php 里实现了这个， 最简化的实现。

## DNException 异常类 | trait DNThrowQuickly
使用 trait DNThrowQuickly
```php
MyException::ThrowOn($flag,$message,$code);
```
等价于下面，少写了好多东西
```php
if($flag){throw new MyException($message,$code);}
```
注意到这会使得 debug_backtrace 调用堆栈不同。
你自己的异常类应该 use DNThrowQuickly 没必要继承 DNException。
原因是你应该只处理你自己熟悉的异常


## DNRoute 路由类
这应该会被扩展,加上权限判断等设置
路由类是很强大扩展性很强的类。

    _URL($url=null)
    _Parameters()
    init($options)
    run()
    set404($callback)
    defaultURLHandler()
    set404 设置404 回调

    protected getRouteHandleByFile
    protected getObecjectToCall($class_name)
    protected getMethodToCall($obj,$method)
文件模式的路由

    public  assignRoute($key,$callback=null)
映射模式的路由。

    getRouteCallingPath()
    getRouteCallingClass()
    getRouteCallingMethod()
以上三组，是当前路径，当前类，当前方法。
当前方法用于权限的判断。如跳过login 方法其他都要权限。
当前类如果为空，说明是 rewrite 过来的。
当前路径用于如果是切片的，找回未切片的路径。

高级模式

setURLHandle
    
    替换 URL()函数的实现。
addRouteHook
    
    添加路由的hook,$preprend  在最前面加 
## DNView 视图类
    public function _ExitJson($ret)
    public function _ExitRedirect($url,$only_in_site=true)
    public function _Show($data=[],$view)

    public function init($path)
    public function setBeforeShow($callback)
    public function setViewWrapper($head_file,$foot_file)
    public function showBlock($view,$data)
    public function assignViewData($key,$value=null)
    protected function includeShowFiles()

## DNConfiger 配置类
    public function init($path)
    protected function include_file($file)
    public function _Setting($key)
    public function _Config($key,$file_basename='config')
    public function _LoadConfig($file_basename='config')

    DNConfiger 类获得配置设置
## 异常管理 trait DMMVCS_ExceptionManager
    异常管理已经变更，文档待补完

## DNDBManger 数据库管理类
DNMVCS 采用
这个也许会经常改动。比如用自己公司的 DB 类，要在这里做一个封装。

installDBClass($db_create_handler,$db_close_handler)

    安装DB类
    $db_create_handler($config,$tag) 返回 DB 实例。方便扩展
    setting 里的 db, db_r 会传到这里。
    
    $db_close_handler($db,$tag) 关闭数据库

closeAllDB()

    关闭所有数据库，在显示输出之前关闭
## DNDB 类
DNMVCS 自带了一个简单的 DB 类。
DN::DB()得到的就是这个 DNDB 类。
DB 的配置在 setting.sample.php 里有。
$db 和 $db_r ，如果是读取的数据库，则用 $db_r 字段。
DNDB 简单实现的一个数据库类。封装了 PDO， 和 Medoo 兼容，也少了 Medoo 的很多功能。
下面主要说 DB 类的用法
```
pdo 这是个公开成员变量而不是方法，是的，你可以操作 pdo
close
    关闭数据库
public function quote($string)
    转码,如果是数组，则值部分会转码。
public function fetchAll($sql,...$args)
public function fetch($sql,...$args)
public function fetchColumn($sql,...$args)
    这三个是动态参数，直接查询
    获得的是数组
    （有时候还是觉得直接用 object $v->id 之类方便多了,你可以在 pdo 里调整。
public function execQuick($sql,...$args)
    执行 pdo 结果，获得 PDOStatement 为什么不用 exec ? 因为  Medoo用了。
    返回  PDOStatement 对象
public function  rowCount()
    获得结果行数
public function init($config)
    初始化
protected function check_connect()
    DNDB 是使用的时候才连接的，不是一上来就连接数据库
public static function CreateDBInstance($db_config)
    用于创建DB类
```
## DNAutoLoader 加载类
DNAutoLoader 不建议扩展。因为你要有新类进来才有能处理加载关系，不如自己再加个加载类呢。

    init($options)
    run()
DNAutoLoader 做了防多次加载和多次初始化。

# 额外的类
## DNMedoo.php
DNMedoo 是 Medoo 的一个简单扩展，和 DNDB 接口一致。
因为 DNMedoo 对 Medoo 有依赖关系，所以单独放在一个文件。
DNMedoo 类的除了默认的 Medoo 方法，还扩展了 DNDB 类同名方法。

### 使用方法：
在你的 DNMVCS->init() 后面段加上下面代码，
使得 DNMedoo 替换 DNDB
```php
self::Import('Medoo');  //请选择正确的 Medoo 载入方式
self::ImportSys('DNMedoo'); //DNMedoo 依赖 Medoo，所以需要手动加载
\DNMVCS\DNMVCS::G()->installDBClass(
    [\DNMVCS\DNMedoo::class,'CreateDBInstance']
    [\DNMVCS\DNMedoo::class,'CloseDBInstance']
);
```
其中 DNMedoo extends Medoo implement IDNDB.


## DNMVCSExt.php  | 额外类应用和说明
    DNMVCS 的选项 $options['ext'] 不为空数组就 引入
    配置字段 ext 数组有数据的时候，会进入高级模式。自动使用扩展文件
    这些功能，用于，1 单一文件解决问题，2 多工程配置，3 使用更好的 db

### 额外模式
```php
const DEFAULT_OPTIONS_EX=[
    'key_for_simple_route'=>'_r', //act 这个选项，不用 path_info 了，我们用 $_REQUEST['act']，
    
    'use_function_view'=>false,   //不用 view 文件了，我们用 view_$xx 来表示view
        'function_view_head'=>'view_header', // 页眉函数
        'function_view_foot'=>'view_footer', // 页脚函数
    'use_function_dispatch'=>false, //路由上不用 DNController->$xx 了，直接 action_$xx
    'use_common_configer'=>false,  //额外配置文件，多工程共享配置用
        'fullpath_project_share_common'=>'',  //配合上面的使用， 公共文件会被本工程覆盖
    'use_common_autoloader'=>false,  // 额外 loader ，多工程共享配置用
        'fullpath_config_common'=>'',  //配合上面的使用， 公共文件会被本工程覆盖
    'use_ext_db'=>false,  //使用 \DNMVCS\DBExt 代替  \DNMVCS\DNDB
    'use_strict_db_manager'=>false, // 严格模式
    'use_superglobal'=>false, //用 SuperGlobal 代替默认的 超级变量。
];
```
'fullpath_config_common'=>'',  
    DNConfiger::G(ProjectCommonConfiger::G()); // 
    设置和配置会先读取相应的文件，合并到同目录来
'fullpath_project_share_common'=>''     // 通用类文件目录，用于多工程
    ProjectCommonAutoloader::G()->init(DNMVCS::G()->options)->run();
    只处理了 CommonService 和 CommonModel 而且是无命名空间的。

### 严格模式
我想让 DB 只能被 Model , ExModel 调用。Model 只能被 ExModel,Service 调用 。 LibService 只能被Service 调用  Service只能被 Controller 调用

可以,你的 Service  继承 StrictService. Model 继承 StrictModel  初始化里 加这一句
use_strict_db_manager
严格模式下那些 **新手** 就不能乱来了。


为什么不作为框架的默认行为。 主要考虑性能因数，而且自由，无依赖性
### StrictService
    你的 Service 继承这个类
    调试状态下，允许 service 调用 libservice 不允许 service 调用 service ,不允许 model 调用 service
### StrictModel
    你的 Model 继承这个类
    调试状态下，只允许 Service 或者 ExModel 调用 Model
### StrictDBManager
    包裹 DNDBManger::G(DNMedoo::W(DNDBManger::G())); 后，实现
    不允许 Controller, Service 调用 DB
    如果使用 Medoo ，请在 installDBClass(DNMedoo::class); 后面执行。
### DBExt
    加了额外方法的DB类，注意和 Medoo 不兼容
    多出的方法有 
    quote_array， get， insert， update， delete
    等
    user_ext_db 选项自动安装，手动安装用
    \DNMVCS\DNDBManager::G()->installDBClass([DBExt::class,'CreateDBInstance']， [DBExt::class,'CloseDBInstance']);
### ProjectCommonAutoloader
    实现通用文件加载
### ProjectCommonConfiger
    实现通用配置加载
### FunctionDispatcher
    函数方式的 controller
### FunctionView
    函数方式的 view

## Tookit.php 未使用用于参考的工具箱类。

### trait DNWrapper 
W($object);
    
    DNWrapper::W(MyOBj::G()); 包裹一个对象，在 __call 里做一些操作，然后调用 call_the_object($method,$args)
    未使用。
### trait DNStaticCall

    Facade 的trait 引用到 DNSingleton，由于 php7 的限制， protected funtion 才能 static call
    未使用
### SimpleRoute SimpleRoute
    未使用,仅供参考，请用 SimpleRouteHook
### SimpleRouteHook
    SimpleRoute 用于指定 _GET 里某个 key 作为 控制器分配.
    使用 $options['key_for_simple_route'] 来打开他。

### MedooSimpleIntaller
    \DNMVCS\DNDBManager::G()->installDBClass([DBExt::class,'CreateDBInstance']， [DBExt::class,'CloseDBInstance']);
    用于加载 medoo 类代替默认的 db 类，注意 medoo 类 不兼容默认 db 类
### API
    用于 api 服务快速调用 无引用
    public static function Call($class,$method,$input)  input 是关联数组
    protected static function GetTypeFilter() 重写这个方法限定你的类型
    在项目里未使用
### MyArgsAssoc
    GetMyArgsAssoc 获得当前函数的命名参数数组 无引用
    CallWithMyArgsAssoc($callback)  获得当前函数的命名参数数组并回调
    在项目里未使用
### DNFuncionModifer
    包裹函数，实现 aop

----


## DNRouteAdvance.php
    这个文件是用于自定义 route 和 rewrite 的
## SuperGlobal.php
    对超全局数组的封装

## SwooleSuperGlobal
    超全局数组的 swoole 替换层
## DNSwooleHttpServer
    Swoole 的 Http 服务器,单独章节介绍

# DNMVCS 的代码流程讲解

大致用图表现如下
```
DN::init
    autoload 自动加载
    checkOverride 如果子类，则 G函数替换为子类。
    initExceptionManager 初始化异常。
    initConfiger,initView,initRoute,initDBManager 初始化组件

DN::run
    RouteAdvance->hook
    (DNRoute::run)
    (RouteHook)($this);
         
    getRouteHandleByFile
    (DNRoute->callback)()

DN::DB
    DBManager::installDBClass
```
# 常见问题

- Session 要怎么处理 
    一般来说 Session 的处理，放在 SesionModel 里。在构造函数里做 session_start 相关代码

- 后台里，我要判断权限，只有几个公共方法能无权限访问
    - 构造函数里获得 $method=DNMVCS::G()->getRouteCallingMethod(); 然后进行后处理
    
- 为什么不把 DNMVCS 里那些子功能类作为DNMVCS类的属性， 如 $this->View=DNView::G();
    - 静态方法里调用。 self::G()->View->_Show() 比 DNView::G()->_Show() 之类更麻烦。非静态方法里也就懒得加引用了
- 我用 static 方法不行么，不想用 G() 函数于 Model ,Service
    - 可以，Model可以用。不过不推荐 Service 用
    - 琢磨了一阵如何不改 static 调用强行塞  strict 模式，还是没找到方法，切换 namespace 代理的方式可以搞定，但还是要手工改代码.

!!!2018-09-30 12:09:57 已经想出来了，改 autoloader ，配合 class alias 。测试 DEMO已过，有空添加
!!!2018-10-02 21:10:09 失败，因为 alias 之后，还要调用原来的类。
    - DNStaticCall 由于 php7 的限制， protected funtion 才能 static call
- 思考：子域名作为子目录
    想把某个子目录作为域名独立出去。只改底层代码如何改
    或者 v1/api v2/api 等等
- error-exception 和 error-500 有什么不同
    error-500 是引入的文件有语法错误之类。 error-exception 是抛出了错误没处理，用 setExceptionHandle 可以自行处理。

- 为什么不拆分文件，按 composer ,psr-4 目录布局
    因为不想太多零碎文件，而且还没想到什么应用要拆分

## 和其他框架的整合

```php
<?php
$options['error_404']=function(){};
$flag=DNMVCS::G()->init($options)->run();
if($flag){return;}
// 其他框架代码
```


# Swoole 整合指南
## DNSwooleHttpServer
DNSwooleHttpServer 是设计成几乎和 DNMVCS 无关的框架。

DNSwooleHttpServer 和 DNMVCS 主类主要关系是在 G 函数 的实现，如果没这个 G 函数，两者是完全独立的。
DNSwooleHttpServer  重写了 G 函数的实现，使得做到协程单例。
还记得 _SERVER,_GET,_POST 超全局变量在 swoole 协程下无法使用么。
你要用 DNMVCS\SuperGlobal\SERVER:: Get($key), Set($key,$value)代替

## 三种模式

### 作为和DNMVCS无关的 DNSwooleHttpServer
如果 http_handler 为空，有 http_handler_file 则直接 include  http_handler_file 运行，和 DNMVCS 系统无关

$server_options 的选项
```php
const DEFAULT_OPTIONS=[
        'swoole_server'=>null, // swoole_http_server 对象，留空，则用 host,port 创建
        'swoole_options'=>[],   //swoole_http_server 的配置，合并如 server
        
        'host'=>'0.0.0.0',  // IP
        'port'=>0,          //端口
        
        'http_handler_root'=>null,      // php 的目录和静态目录的不相同，留空
        'http_handler_file'=>null,      // 启动文件 留空将会使用 http_handler
        'http_handler'=>null,           // 启动方法，
        'http_exception_handler'=>null, // 异常处理方法,DNMVCS 已经占用  // http_handler_root 的异常也是这里处理
        
        'websocket_open_handler'=>null,  //websocket 打开
        'websocket_handler'=>null,          //websocket 
        'websocket_exception_handler'=>null,    //websocket 异常处理
        'websocket_close_handler'=>null,        //websocket 关闭
];
```
swoole 下， DNMVCS  入口选项 ['swoole']的选项
```php
const DEFAULT_DN_OPTIONS=[
        'not_empty'=>true,  //用于数组不空
        'db_reuse_size'=>0,                 // 大于0表示复用数据库连接
        'db_reuse_timeout'=>5,              // 复用数据库连接超时秒数

    ];
```

想要获得当前 的 request ,response 用 DNSwooleHttpServer::Request() ,Response（）；
还记得 _SERVER,_GET,_POST 超全局变量在 swoole 协程下无法使用么。
你要用 DNMVCS\SuperGlobal\SERVER:: Get($key), Set($key,$value)代替
exit 函数可以用。但 header 函数不能用了，你得用 DNSwooleHttpServer::G()->header .还有 setcookie ,set_exception_handler 类似。

DNSwooleHttpServer 运行 DNMVCS 可以有三种模式
1. http_handler_root
    这和document_root 一样。读取php文件，然后运行的模式
2. http_handler_file
    这种模式是把 url 都转向 文件如 index.php 来处理
3. http_handler
    所有url请求都到这个函数处理。
    重点模式
    DNSwooleHttpServer->bindDN($dn_options) 就是把请求定到 DNMVCS 里处理。
    这模式和上面两种模式的区别，就是常驻内存,
    然后设置  http_handler http_exception_handler 为 DNMVCS  的 run, onException


DNSwooleHttpServer 可以让你用 echo 直接输出。

http_exception_handler，单文件模式和目录模式，你可以在这里处理 404。

### DNMVCS 整合到 DNSwooleServer

```php
<?php

\DNMVCS\DNMVCS::RunAsServer($server_options,$dn_options);
// 展开模式 \DNMVCS\DNSwooleServer::G()->init($server_options,$server)->bindDN($dn_options)->run();

DNSwooleHttpServer::()->init($server_options);
```
## class DNSwooleHttpServer
    public function init($server_options,$server=null)
    public function bindDN($dn_options)
    public function run()
    public static function RunWithServer($server_options,$dn_options=[])

### trait DNSwooleHttpServer_Static

静态函数列表

    public static function Server()

    获得当前 swoole_server 对象
    public static function Request()

    获得当前  swoole_request
    public static function Response()

    获得当前  swoole_response    
    public static function Frame()

    获得当前  frame （websocket 生效 ）  
    public static function FD( 生效)

    获得当前  fd  （websocket 生效）
    public static function IsClosing()
    判断是否是关闭的包 （websocket 生效）

    public static function CloneInstance()

    把主进程单例复制到协程单例
    
## trait DNSwooleHttpServer_GlobalFunc
全局函数的替代。 作为 DNSwooleHttpServer 的一部分
对应PHP手册的函数

    header(string $string, bool $replace = true , int $http_response_code =0)
    setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)
    set_exception_handler(callable $exception_handler)
    register_shutdown_function(callable $callback,...$args)

## class DNSwooleException
    public static function ThrowOn($flag,$message,$code=0)
404 错误是用 code=404 那个
没端口会报错


## class DBConnectPoolProxy
DB连接代理
    public function setDBHandler($db_create_handler,$db_close_handler=null)
    public function onCreate($db_config,$tag)
    public function onClose($db,$tag)

## class SwooleContext
    协程单例。Swoole 的运行信息


## trait DNSwooleHttpServer_SimpleHttpd
单独使用这个 trait 你可以实现一个 http 服务器
public function onRequest($request,$response)
## trait DNSwooleHttpServer_WebSocket
public function onRequest($request,$response)
单独使用这个 trait 你可以实现一个 websocket 服务器

## class CoroutineSingleton
用于协程单例
    public static function GetInstance($class,$object)
    public static function CreateInstance($class,$object=null)
    public static function CloneInstance($class)
    public static function DeleteInstance($class)
    public static function ReplaceDefaultSingletonHandler()
    public static function CleanUp()
    public static function Dump()

## SuperGlobal


    SuperGlobal::GET($k) POST COOKIE ... 是用于超全局变量无法使用的 swoole 环境中， 也可以在 fpm 下使用
    以上是读取，写入是用 SuperGlobalGET::Set($k,$v)  
    写入的数据不改变系统超全局变量数据
    swoole 条件下，你要用 DNSwooleHttpServer::setCookie 来改变 cookie
    SuperGlobal::StartSession SuperGlobal::DestroySession 用于 session 开关,在 swoole 下代替 session_start 和 session_destroy

 # DNMVCS 是怎么越做越复杂的

    一开始想解决的是 MVC 缺 service 层
    接下来是偷懒选项
    接下来是一个文件搞定
    接下来是为了组件可灵活替换
    接下来是为了默认的几个组件 内部组件用户不必知道，使用即可
    接下来是数据库管理，支持主从和可替换化
    接下来是要应付额外的一些功能,这在 DNMVCSExt 里
    接下来是为了高端路由。——功能太大，不放到主类里。

    接下来是支持 swoole 
    支持 swoole 需要 superglobal 选项。
    swoole 的 session还要单独写
    代码就这么多了。
