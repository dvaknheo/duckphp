# DNMVCS 介绍
## DNMVCS 是什么
一个 PHP Web 简单框架 
* 主要卖点：比通常的 Model Controller View 多了 Service 。拟补了 常见 Web 框架少的缺层。
这个缺层导致了很糟糕的境地。你会发现很多人在 Contorller 里写一堆代码，或者在 Model 里写一堆代码。
使得网站开发者专注于业务逻辑。

* 为偷懒者写的。只需要引用一个文件，不做一大堆外部依赖。composer 安装正在学习中。
* 小就是性能。
* 替代 Codeiginter 这个PHP4 时代的框架，只限于新工程。
* 不仅仅支持全站路由，还支持局部路径路由和非 PATH_INFO 路由,不需要配服务器也能用
* 耦合松散，扩展灵活方便，魔改容易

## 关于 Servivce 层
MVC 结构的时候，你们业务逻辑放在哪里？
新手放在 Controller ，后来的放到 Model ，后来觉得 Model 和数据库混一起太乱， 搞个 DAO 层吧。
可是 一般的 PHP 框架不提供这个功能。
所以，Service 按业务走，Model 层按数据库走，这就是 DNMVCS 的理念。
DNMVCS 的最大意义是思想，只要思想在，什么框架你都可以用
你可以不用 DNMVCS 实现 Controller-Service-Model 架构。
只要有这个思路就成功
## 简化的 DNMVC 层级关系图

```
           /-> View
Controller --> Service ---------------------------------> Model   
                      \               \              /
                       \-> LibService --> ExModel --/
```
* Controller 按 URL 入口走 调用 view 和service
* Service 按业务走 ,调用 model 和其他第三方代码。
* Model 按数据库表走，只实现和当前表相关的操作。有些时候
* View 按页面走
* 不建议 Model 抛异常
1. 如果 Service 相互调用怎么办?
添加后缀为 LibService 用于 Service 共享调用，不对外，如MyLibService
2. 如果跨表怎么办?，两种解决方案
    1. 在主表里附加
    2. 添加后缀为 ExModel 用于表示这个 Model 是多个表的，如 UserExModel。或者单独和数据库不一致如取名 UserAndPlayerRelationModel

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

## DNMVCS 不做什么
* ORM ，和各种屏蔽 sql 的行为，根据日志查 sql 方便多了。 自己简单封装了 pdo 。
* 模板引擎，PHP本身就是模板引擎
* Widget ， 和 MVC 分离违背
* 接管替代默认的POST，GET，SESSION 系统提供给你就用，不要折腾这些。



## DNMVCS 还要做什么

* composer 安装模式，本人还没学会
* 范例，例子还太简单了

## DNMVCS 的 缺点

1. 不优雅。万恶之源。 
2. 调用堆栈层级太少，不够 Java 。
3. 虽然实现了标准的 PSR 规范实现，但是还给懒鬼们开了后门。
4. 错误报告页面很丑陋。 想华丽自己写一个。不用 IDE 的直接看就懂。
5. 没有中间件。 重写 controller 啊，要什么中间件。
6. 没有强大的全局依赖注入容器，只有万能的 G 函数。
7. 没有灵活强大的 AOP ，只有万能的 G 函数。
8. 这框架什么都没做啊。 居然只支持 PHP5.6+，甚至 PHP7 


## 还有什么要说的

使用它，鼓励我，让我有写下去的动力

# DNMVCS 使用手册
## 入门
### 第一步
把 web 目录设置为 DNMVCS sample/www 目录 复制 config/setting.sample.php 为 config/setting.php
浏览器中打开主页出现欢迎页面就表示执行完成
```
Hello DNMVCS

Time Now is 2018-06-14T22:16:38+08:00
```
如果漏了修改 config.setting.php 会提示：
```
DNMVCS Notice: no setting file!,change setting.sample.php to setting.php !
```
### 后续的工作
新建工程怎么做？ 复制 sample 目录到你工程目录就行，修改 index.php ，使得引入正确的库

还有哪些没检查的？ 服务器配置 PATH_INFO 对了没有。 数据库也没配置和检查。
想要更多东西，可以检出  dnmvcs-full 这个工程，里面有全部的测试样例。 *尚未完成*

开始学习吧

### 目录结构
工程的目录结构
```
+---app // psr-4 标准的自动加载目录
|   +---Controller  // 路由控制器
|   |       Main.php    // 默认控制器入口文件
|   +---Model       // 模型放在里
|   |       TestModel.php   // 测试 Model 
|   +---Service     // 服务放在这里
|   |       TestService.php //测试 Service
|   \---System   // 基类放在这里
|          App.php    // 默认框架入口文件
+---classes         //自动加载的类，放在这里
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
\---www             //  网站目录放这里
        index.php // 主页面
```
解读

www/index.php  入口 PHP 文件,内容如下
```php
<?php
require('../../DNMVCS/DNMVCS.php');
\DNMVCS\DNMVCS::RunQuickly();

//$path=realpath('../');
//\DNMVCS\DNMVCS::G()->autoload(['path'=>$path])->init([])->run();
```
被注释掉部分 和 实际调用部分实际相同。是个链式调用。
DNMVCS\DNMVCS::G(); 单例模式。 
DNMVCS\DNMVCS 主类，在后面有好多其他方法详细介绍。
这些方法背后是不同的你可以改写的类。

autoload(['path'=>$path]);  注册加载类 拆分出来是为了方便扩展子类化处理。
init([]);初始化，这部分入口配置见后面章节
run(); 开始路由

## 简单入门

深入的级别
1. 使用默认选项实现目的 
2. 只改配置实现目的
3. 继承接管特定类实现目的
4. 魔改。

## 选项
init([]) 方法的参数是可配置的，默认设置如下
默认选项
```php
[
    'namespace'=>'MY',  // 默认的命名空间
    'enable_simple_mode'=>true, //启用无命名空间模式
    'enable_paramters'=>true,   //启用 paramter 切分
    'fullpath_config_common'=>'',  // 共享配置的目录
    'fullpath_framework_common'=>'', 
    //无命名空间下， CommonModel,CommonService 后缀的绝对加载路径用于多网站配合的情况。
];
```
** 一些高级配置，用于魔改的请自己暂时去翻看代码。 *
启用无命名空间模式 ，就是不想写那么多带命名空间的代码， *Service,  *Model 这样结尾的类直接自动加载
### 全部默认选项详解。
    多余的缩进里的选项是不建议修改的
```php
$default_options_autoload=[
    'namespace'=>'MY', // 默认的命名空间改成你的工程名字
        'path_namespace'=>'app', 	// 命名空间根目录
        'path_autoload'=>'classes',	// 无命名空间的类存放目录
    'fullpath_framework_common'=>'' // 通用类文件目录，用于多工程
    'enable_simple_mode'=>true, 	// 简单模式，无命名空间直接 controller, service,model
        'path_framework_simple'=>'app', // 简单模式的基本目录
];

$default_options_route=array(
    'namespace'=>'MY',	 // 默认的命名空间改成你的工程名字
	'enable_paramters'=>false, //支持切片模式
    'enable_simple_mode'=>true, //共享简单模式
        'path_controller'=>'app/Controller', //controller 的目录
        'namespace_controller'=>'Controller',   //controller 的命名空间
        'default_controller_class'=>'DNController', //默认controller 名字
        'enable_post_prefix'=>true, // 把 POST 的 映射为 do 方法
        'disable_default_class_outside'=>false, // 屏蔽  Main/index  第二访问模式
    'key_for_simple_route'=>null,   //切换成支持 _GET  模式路由 _r=about/foo 这样的
);

$default_options_system=[
    'system_class'=>null,   // override 重写 系统入口类代替 DNMVCS 类。
	
    'fullpath_config_common'=>'', // 通用配置的目录，用于多工程
        'path_view'=>'view',  //视图目录
		'path_lib'=>'lib',  // 用于 import
		'path_config'=>'config',    // 配置的路径
	'use_ext'=>false,  //加载 DNMVCSExt
	'use_ext_db'=>false,  //用 DBExt 代替 DNDB 数据库类
];
```
### 设置文件
工程的设置文件样例 setting.sample.php 。选项很少

```php
<?php
$data=array();
$data['is_dev']=true;
$data['db']=array(
	'dsn'=>'mysql:host=???;port=???;dbname=???;charset=utf8;',
	'username'=>'???',
	'password'=>'???',
);
return $data;
```
    只有一个设置项目 is_dev 用于 判断是否是开发状态，默认并没使用到，在额外库里用到
    db，配置数据库。
    db_r， 配置读写分离的数据库

### 选项，设置，配置的区别
    选项，代码里的设置
    设置，敏感信息
    配置，非敏感信息

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
在控制器里，我们调用了 MiscService 这个服务。
MiscService 调用 MiscModel 的实现。此外，我们要调整 返回值的内容
我们用 DNSingleton单例。

::app/Service/MiscService.php
```php
<?php
// 
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

::app/Model/MiscModel
```php
// 
class MiscService
{
    use \DNMVCS\DNSingleton;
    public function getTime()
    {
        return DATE(DATE_ATOM);
    }
}
```
这就是 DNMVC 下的简单流程了。其他开发类似。

## 理解路由和控制器
DNMVCS 的控制器有点像CI，不需要继承什么，就这么简单。
1
甚至连名字都不用，用默认的 DNController 就够了。
而且支持子命名空间多级目录。如果开启简单模式，也可用 __代替 \ 切分。
2
DNController 重名了怎么办，比如我要相互引用？ 
1 那是你不应该这么做，2 你也可以采取名称对应的类，而不偷懒啊啊。

3 DNMVCS 还支持路由映射。 
正则用 ~
要指定 GET/POST 在最前面加http 方法.

    DN::assignRoute('GET ~article/(\d+)','article->get');
    *用->表示类调用而不是警惕调用
DNMVCS 支持 Paramter，你可以在设置里关掉。
Parameter 切片会直接传递进 方法里作为参数
路由表里，用正则切分会传递进方法，不管是否开启 enable_paramters

比如 路由不用 path_info 用 $_GET['_r'] 等，很简单的。
simple_route_key 开启 _GET 模式路由（原先是在单独类里实现，后来整合了
如果你想加其他功能，可以继承 DNRoute 类。 

路由这块很多东西，300 行代码不是这么简单描述的

run() 方法开始使用路由。 如果你不想要路由。只想要特定结构的目录， 不调用 run 就可以了。
    比如我一个样例，只想要 db 类等等。
## 重写 错误页面
错误页面在 view/_sys 里。你可以修改相应的错误页面方法。
比如 404 是 view/404.php
高级一点，你可以 扩展 DNMVCS 的主类实现

# DNMVCS 主类
## 基本方法
```
static G($object=null,$args=[])   
    G 单例函数是整个系统最有趣的地方。
    传入 $object 将替代默认的单例。
	比 PHP-DI简洁，后面的文档 会有详细介绍
static RunQuickly($optionss=[])
    DNMVCS::RunQuickly ($options) 相当于 DNMVCS::G()->init($options)->run();
    默认配置 'framework_class'=>'\MY\\Framework\App' 如果有 framework_class 那就用来子类化。
autoload($optionss=[])
    自动加载。处理自动加载机制。 得找到自动加载才把子类化的文件载入进来，所以这个方法单列出来。
init($options=[])
    初始化，这是最经常子类化完成自己功能的方法。
    如果在初始化之前没有 autoload 会在这里执行。
    如果已经执行了 autoload 会把 默认配置合并 autoload 的配置以及参数的配置作为配置使用。
    你可以扩展这个类，添加工程里的其他初始化。
run()
    开始路由，执行。这个方法拆分出来是为了，不想要路由，只是为了加载一些类的需求的。
```
'system_class'=>'\MY\System\App'  可以在 init 方法里用，使得替换默认类。

## 常用静态方法方法
这些方法因为太常用，所以静态化了。
包括 视图view,路由，数据库，配置 ，

Show($data=array(),$view=null)

    显示视图 实质调用 DNView::G()->_Show();
    视图的文件在 ::view 目录底下.
    为什么数据放前面，DN::Show(get_defined_vars());把 controller 的变量都整合进来，并用默认路径作为 view 文件。
    高级开发者注意，这里的 $view 为空是在静态方法里处理的，子类化需要注意
DB()

    返回数据库,实质调用 DBManager::G()->_DB();
    数据库管理类 DNManager 里配置的
DB_W()

    返回写入的数据 实质调用 DBManager::G()->_DB();
    默认和 DB() 函数一样
DB_R()

    读取用的数据库 和默认配置一样。
URL($url=null)

    获得调整路由后的url地址 实质调用DNRoute::G()->_URL();
    当你重写 DNRoute 类后，你可能需要重写这个方法来展示
    比如 simple_route_key 开启后， URL('class/method?foo=bar') 
    将会是 ?r=class/method&foo=bar ，而不是 /class/method?foo=bar
_Parameters()

    获得路径切片 实质调用 DNRoute::G()->_URL();
    当用正则匹配路由的时候，匹配结果放在这里。
    如果开启了 eanbale_parameter 匹配选项也会在这里。
Setting($key)

    读取设置 实质调用 DNConfig::G()->Setting();
    设置在 ::/config/setting.php 里，php 格式
    配置非敏感信息，放在版本管理中，设置是敏感信息，不存在版本管理中
GetConfig($key)

    读取配置 实质调用 DNConfig::G()->GetConfig();
    配置放在 config/config.php 里，php 格式
    配置非敏感信息，放在版本管理中，设置是敏感信息，不存在版本管理中
LoadConfig($file_basename)

    加载其他配置 实质调用 DNConfig::G()->LoadConfig();
    如果很多配置文件，手动加载其他配置
ExitJson($ret)

    打印 json_encode($ret) 并且退出 实质调用 DNView::G()->ExitJson();
    这里的 json 为人眼友好模式。
ExitRedirect($url)

    跳转到另一个url 并且退出 实质调用 DNView::G()->ExitRedirect();
ExitRouteTo($url)

    跳转到 URL()函数包裹的 url。
    应用到 DNView::G()->ExitRedirect(); 和 DNRoute::G()->URL
    高级开发者注意，这是静态方法里处理的，子类化需要注意
ThrowOn($flag,$message,$code);

    如果 flag 成立则抛出 DNException 异常。 调用 DNException::ThrowOn
    减少代码量。如果没这个函数，你要写
    if($flag){throw new DNException($message,$code);}
    折腾
    如果是你自己的异常类 ，可以 use DNThrowQuickly 实现 ThrowOn 静态方法。
Import($file)

    手动导入默认lib 目录下的包含文件 函数 实质调用 self::G()->_Import();
ImportSys($file)
    
    手动导DNMVCS目录下的包含文件 函数。DNMVCS库目录默认不包含其他非必要的文件
	因为需求不常用，所以没自动加载
	比如在调试状态下的奇淫巧技：限定各 G 函数的调用。以及DNMedoo ，用 Medoo类
H($str)

    html 编码 这个函数常用，所以缩写。用了 utf-8的模式
RecordsetH($data,$cols=[])

    给 sql 查询返回数组 html 编码，
RecordsetURL($data,$cols_map) 

    给 sql 返回数组 加url 比如  url_edit=>"edit/{id}",则该行添加 url_edit =>"edit/1" 等类似。


## 非静态方法
这里的方法偶尔会用到，所以没静态化 。

在 controller 的构建函数，你可能会用到。
assign 系列函数，都有两个模式 func(\$map)，和 func(\$key,\$value) 模式方便大量导入。

```
assignRoute($route,$callback=null)
    给路由加回调。实质调用 DNRoute::G()->assignRoute
    关于回调模式的路由。详细情况看介绍
getCallingMethod()
    获得路由中正在调用的方法。
    用于控制器里判断方法以便于权限管理。
    也适用于重写URL后判断是否是直接访问
setViewWrapper($head_file=null,$foot_file=null)
    给输出 view 加页眉页脚 实质调用 DNView::G()->setViewWrapper
    view 里的变量和页眉页脚的域是一样的。
assignViewData($key,$value=null)
    给 view 分配数据，实质调用 DNView::G()->assignViewData
    这函数用于控制器构造函数添加统一视图数据
showBlock($view,$data)
    展示一块 view ，用于调试的场合。实质调用 DNView::G()->showBlock
    展示view不理会页眉页脚，也不做展示的后处理，如关闭数据库。
    注意这里是 $view 在前面， $data 在后面，和 show 函数不一致哦。
assignExceptionHandel($classes,$callback=null)
    分配特定异常回调。
    用于控制器里控制特定错误类型。
setDefaultExceptionHandel($calllback)
    接管默认的异常处理，所有异常都归回调管，而不是显示 500 页面。
    用于控制器里控制特定错误类型。
isDev()
    实际读设置里的 is_dev ，判断是否在开发状态。
```
## 事件方法
实现了默认事件回调的方法。扩展以展现不同事件的显示。

```
onBeforeShow()
    在输出 view 开始前处理，默认只是关闭数据库。
    因为如果请求时间很长，页面数据量很大。没关闭数据库会导致连接被占用。
onShow404()
    404 回调。这里没传各种参数，需要的时候从外部获取。
onException($ex)
    发生未处理异常的处理函数。显示 exception 页面
onErrorException($ex)
    处理错误，显示500错误。
onDebugError($errno, $errstr, $errfile, $errline)
    处理 Notice 错误。 TODO  这里应该 不仅是 Notice ，还有其他类型也加进来
onErrorHandel($errno, $errstr, $errfile, $errline)
    处理 PHP 错误
```

# 进一步扩展
## 总说
DNMVCS 是用各自独立的类合起来的
各类接口可参加 DNInterface.php，没去加载，因为只有参考意义，没实际意义。
各类之间是独立的

DNMVCS 主类里一些函数，是调用其他类的实现。基本都可以用 G 方法替换
DNMVCS 的各子类都是独立的。现实中应该不会拿出来单用吧
## trait DNSingleton | 子类化和 G 方法
```
trait DNSingleton
    static G();
    static _before_instance($object)
    static _create_instance($class)
```
G 函数，单例模式。
来自 

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
**注意但是静态方法不替换，请注意这一点。 DNMVSC::Show() 和 DNView::Show) 的差异注意一下**

为什么不是 GetInstance ? 因为太长，这个方法太经常用。

所以你可以扩展各种内部类以实现不同功能。

比如你要自己的路由方法在 autoload 类后，init 里。
```php
public function init($options=[])
{
    DNRoute::G(MYRoute::G());
    parent::init($options);
}
```
这样 MYRoute 就接管了 DNRoute 了。

DNView::G(AdminView::G()); 这样 AdminView 就接管了 DNView 了。

G 函数的缺点：IDE 上没法做类型提示，这对一些人来说很重要。

service , model 上 用  static 函数代替 G 函数实例方式或许也是一种办法

_before_instance($object) 被 G 函数调用，返回 $object。用于一些扩展

_create_instance($class) 被 G 函数调用，用于创建实例，如果你的类构造方法带参数，需要重新写这个方法

组件在后续使用，记得初始化：

    在 Controller 里想替换 DNView ，记得在之前初始化
    MYView::G()->init(DNView::G()->path);
    DNView::G(MYView::G());
    或者在 MYView _create_instance 里复制 init 方法过来
## DNAutoLoader 加载类
DNAutoLoader 不建议扩展。因为你要有新类进来才有能处理加载关系，不如自己再加个加载类呢。
    init(options)
    run()
## DNRoute 路由类
    这应该会被扩展,加上权限判断等设置
    
    路由类是很强大扩展性很强的类。单独篇章介绍
## DNView 视图类
    $this->isDev
## DNConfig 配置类
    DNConfig 类获得配置设置
## DNExceptionManager 异常管理类
    异常管理类都是静态方法，基本上没人会接管这个类吧。或者你可以覆盖 DNMVCS 的 init 的方法。
## DNException 异常类 | trait DNThrowQuickly
使用 trait DNThrowQuickly
```
MyException::ThrowOn($flag,$message,$code);
```
等价于下面，少写了好多东西
```
if($flag){throw new MyException($message,$code);}
```
注意到这会使得 debug_backtrace 调用堆栈不同。
你自己的异常类应该 use DNThrowQuickly 没必要继承 DNException。
原因是你应该只处理你自己熟悉的异常

## DNDBManger 数据库管理类
    这里主要是数据库的扩展
    这个也许会经常改动。比如用自己公司的 DB 类，要在这里做一个封装。

installDBClass($callback);

    $callback($config) 返回 DB 实例。方便扩展

## DB 类
DNMVCS 自带了一个简单的 DB 类
DN::DB()得到的就是这个 DNDB 类
DB 的配置在 setting.sample.php 里有。
db 和 db_r ，如果是读取的数据库，则用 db_r 字段。
DNDB 简单实现的一个数据库类。封装了 PDO， 和 Medoo 兼容，也少了 Medoo 的很多功能。
下面主要说 DB 类的用法
```
pdo 这是个公开成员变量而不是方法，是的，你可以操作 pdo
close
    关闭数据库
quote
    转码
quote_array
    对数组转码
fetchAll
fetch
fetchColumn
    这三个是动态参数
($sql,...$args);
    获得的是数组（其实有时候还是觉得直接用 object $v->id 之类方便多了。

execQuick
    执行 pdo 结果，为什么不用 exec ? 因为  medoo用了。
rowCount
    获得结果行数
```
DB 类没扩展 update,insert delete 功能，因为怕和 medoo 的冲突。，todo 把这几个功能放在 DNDBEx 里。
# 额外的类
## DNInterface.php
提供了 DNMVCS.php 里扩展类的接口， PHP 的接口实质只是参照作用。所以没引入。
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
\DNMVCS\DNMVCS::G()->installDBClass('\DNMVCS\DNMedoo');
```
DNMedoo extends Medoo implement IDNDB.


## DNMVCSExt.php  | 额外类应用和说明
DNMVCSExt 的类和方法

    选项 use_ext=true 引入，选项 user_ext_db=true 用 DBext ,额外扩展的db类
### 奇淫巧技
我想让 DB 只能被 Model , ExModel 调用。Model 只能被 ExModel,Service 调用 。 LibService 只能被Service 调用  Service只能被 Controller 调用

可以,你的 Service  继承 StrictService. Model 继承 StrictModel  初始化里 加这一句
```php
\DNMVCS\DNDBManger::G(\DNMVCS\StrictDBManager::W(\DNMVCS\DNDBManger::G()));
```
调试模式下那些 **新手** 就不能乱来了。


为什么不作为框架的默认行为。 主要考虑性能因数，而且自由，无依赖性

### trait DNWrapper 
W($object);
    
    DNWrapper::W(MyOBj::G()); 包裹一个对象，在 __call 里做一些操作，然后调用 call_the_object($method,$args)

### StrictService
    你的 Service 继承这个类
	调试状态下，允许 service 调用 libservice 不允许 service 调用 service ,不允许 model 调用 service
### StrictModel
	你的 Model 继承这个类
    调试状态下，只允许 Service 或者 ExModel 调用 Model
### StrictDBManager
    包裹 DNDBManger::G(DNDebugDBManager::W(DNDBManger::G())); 后，实现
    不允许 Controller, Service 调用 DB
	如果使用 Medoo ，在 DNMedoo::Install(); 后面执行。
### DBExt
	加了额外方法的DB类，注意和 Medoo 不兼容
    多出的方法有 
    quote_array， //str_in_array get， insert， update， delete
    等
    user_ext_db 选项自动安装，手动安装用
    \DNMVCS\DNMVCS::G()->installDBClass('\DNMVCS\DBExt');
### MedooSimpleIntaller
    CreateDBInstance
        用于加载 medoo 类代替默认的 db 类，注意这使得不兼容默认 db 类


### API 用于 api 服务快速调用
	public static function Call($class,$method,$input)  input 是关联数组
	protected static function GetTypeFilter() 重写这个方法限定你的类型

### MyArgsAssoc
- GetMyArgsAssoc 获得当前函数的命名参数数组
- CallWithMyArgsAssoc($callback)  获得当前函数的命名参数数组并回调

# 常见问题

- Session 要怎么处理 
	一般来说 Session 的处理，放在 SessionService 里，这是唯一和状态有关的 Service 例外。
    或者是 SesionModel
	在构造函数里做 session_start 相关代码
- 后台里，我要判断权限，只有几个公共方法能无权限访问
    - 构造函数里获得 $method=DNRoute::G()->calling_method; 然后进行后处理
- 为什么不把 DNMVCS 里那些子功能类作为DNMVCS类的属性， 如 $this->View=DNView::G();
    - 静态方法里调用。 self::G()->View->_Show() 比 DNView::G()->_Show() 之类更麻烦。非静态方法里也就懒得加引用了
- 我用 static 方法不行么，不想用 G() 函数于 Model ,Service
	- 可以，Model可以用。不过不推荐Service 用
	- 琢磨了一阵如何不改 static 调用强行塞  strict 模式，还是没找到方法，切换 namespace 代理的方式可以搞定，但还是要手工改代码.
- 思考：子域名作为子目录
	想把某个子目录作为域名独立出去。只改底层代码如何改

