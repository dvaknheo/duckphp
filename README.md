# DNMVCS
## DNMVCS 是什么
一个 PHP Web 简单框架 比通常的 Model Controller View 多了 Service 。拟补了 常见 Web 框架少的缺层。
这个缺层导致了很糟糕的境地。你会发现很多人在 Contorller 里写一堆代码，或者在 Model 里写一堆代码。
使得网站开发者专注于业务逻辑。

另一点是为偷懒者写的。一个文件夹带走，不做一大堆外部依赖。
小就是性能。

## 关于 Servivce 层
MVC 结构的时候，你们业务逻辑放在哪里？
新手 controller ，后来的放到 model ，后来觉得 model 和数据库混一起太乱， 搞个 Dao 层吧.
所以，Service 按业务走，model 层按数据库走，这就是 DNMVCS 的理念， 还有， 去你的 dao.
## DNMVCS 使用理念
DNMVCS 的最大意义是思想，只要思想在，什么框架你都可以用
简化的 DNMVC 层级关系图
```
		   /-> View
Controller --> Service ---------------------------------------------------> Model   
					  \                \             /
					   \-> LibService --> ExModel --/
```
* Controller 按 url 入口走 调用 view 和service
* Service 按业务走 ,调用 model 和其他第三方代码。
* Model 按数据库表走，只实现和当前表相关的操作。有些时候
* View 按页面走
* 不建议 Model 抛异常
1. 如果 Service 相互调用怎么办?
添加后缀为 LibService 用于 Service 共享调用，不对外，如MyLibService
2. 如果跨表怎么办?

两种解决方案
1. 在主表里附加
2. 添加后缀为 ExModel 用于表示这个 Model 是多个表的，如 UserExModel。或者单独和数据库不一致 UserAndPlayerRelationModel

## DNMVCS 做了什么
* 简单可扩展灵活的路由方式 => 要不是为了 url 美化，我才不做这个。
* 简单的数据库类 => 这个现在推荐整合 Medoo 食用
* 扩展接管默认错误处理 => 你也自己处理异常错误
* 简单的配置类  => setting 就是一个数组， config 就是动态配置
* 简单的加载类  => 只满足自己需要
所有这些仅仅是在主类里耦合。

##  入门(Guide.md)

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

----

## DNMVCS 的目录结构约定
sample 目录就是一般典型目录
```
+---DNMVCS  系统目录，这里面的内容不要修改
```
config 目录 
config.php 是各种配置，无敏感信息

setting.sample.php 改名为 setting.php  是不存放在版本管理系统里的

setting.sample.php 演示了 数据库的配置，和设置是否在调试状态

www 目录

index.php
```
require('../../DNMVCS/DNMVCS.php');
$path=realpath('../');
DNMVCS::G()->run(); //运行
```
另一个版本的
```
require('../../DNMVCS/DNMVCS.php');
$path=realpath('../');
DNMVCS::G()->autoload($path); // autoloader 自动加载器
// 这里就可以子类化了 DNMVCS::G(CoreMVCS::G())  之类
DNMVCS::G(CoreMVCS::G())->init($path)->run();
```
## DNMVCS 使用方法
1. 把 sample/www 配置为你的站点目录。
2. 将 sample/config/setting.sample.php 改为 sample/config/setting.php
3. 用浏览器打开网站，出现 Hello DNMVCS 就成功了。
4. 接下来细心看 sample 目录的代码，怎么用就基本了解了。 
// 解压， setting.php 的 demo 打开， 直接 www/index.php?r=test/test.

* 为什么要步骤2 ？ 设置文件放在版本管理里不安全。 如果没有做步骤二会怎么养？，会有一个报错提示
* 建议做新站点的时候不要更改 sample 目录的文件，而是把 sample 目录内容复制新的目录。
* 我不想做全站，只是做子目录， 这也可以。把 www/index.php 的文件引用调整好就行。




## 关于魔改
## 关于 namespace
## DNMVCS 的各个类说明

### 附属函数
附属函数是为了节省体力活用的
```
H => htmlspecialchars( $str, ENT_QUOTES ); 系统函数太长了，用这个缩写
URL =>DNRoute::URL($url); 在 controller 里用，View 里不严格要求无计算也可使用
```


### DNExcpetion 错误处理类
class DNException extends Exception
        public static function ThrowOn($flag,$message,$code=0)
        如果 $flag为真，则抛出异常。 用于减少 if 语句
        如 MyException::ThrowOn(true,"test",-110);
        等价于 if(true){throw new MyException("test",-110);}
### DnAutoLoad 自动加载类
自动加载函数的类
```
class DNAutoLoad extends DNSingleton
        public function init($path,$path_common='')
        初始化
        设定 mvc 的目录， 和共享目录
        共享目录主要用于多网站配合
        目录中有
        model
        后缀 CommonModel
        service
        后缀 CommonService
        为什么名字这么长
        因为经常用到这么长名字说明你错了
        你应该在子网站的类里扩展这些共享类。
        public function run()
        执行。
```
### DnRoute 路由类
核心的路由类。

```
class DNRoute extends DNSingleton

        public static function URL($url=null)
        路由了，要找到相对的 URL 用这个静态函数
        顺便，这也写成全局函数，方便在 view 里调用。
        尽管我不太支持在 view 里写代码，但这里是为了方便起见

        public static function Param()
        获取路由之后后面的分段。

        public function _URL($url=null)
        静态函数 URL 的实现函数。

        public static function _Param()
        静态函数 Param 的实现函数。

        public function init($path)
        初始化，设定目录

        public function set404($callback)
        设置 404 的回调函数

        public function run()
        运行
        这才开始

        public function defaltRouteHandle()
        默认的路由方法，支持多级子目录路由
        / 会响应到 controller/Main.php 里 index 方法。
        /AA/BB 会响应到 controller/AA.php 的 BB 方法。
        /AA/BB/CC/DD 会响应到 controller/AA/BB.php 的 CC 方法。
        如果有 controller/AA.php ，那么会覆盖 controller/AA/ 目录里的文件就不会被调用

        和通常每个路由类一个名字不同的是，DNMVCS 的控制器类都用 DnController 这个名字。
        而不是单独名字，原因是不希望控制器之间调来调去。
        
        优先使用 psr-4 的模式。命名空间以 DNControllerNamespace 开头的多级子目录。psr-4模式优先。

        Param 的数据也会附到 调用的方法上去
        _ 开头的文件，不会被调用
        __ 开头的方法，不会被调用。
        POST的数据 ，会添加定位到 do_*上。

        public function addDefaultRoute($callback)
        添加其他路由方式，默认的 404 之后你可以在这里添加路由

        public function defaltDispathHandle()
        系统内部调用，默认的分发型路由，类似 nodejs 那种

        public function addDispathRoute($key,$callback)
        添加 分发路由形式的回调,路由表模式适用.
         /ABC => POST 和 GET 都用到 统一到一起
        GET /ABC  => 只用于 GET
        POST ~view([a-z+]) => ~ 开头的是正则，Param 会被正则表达式替换。
        
        $callback 特意添加了如果是 $ 在中间，则自动 new 的方式。
        如 'MyClass$foo' 会对应 new 一个 MyClass 的实例。

        public function mapRoutes($route_array)
        上面方法的合并版本，不用写多条，放一个数组够了。

```
### DNView 视图类
```
class DNView extends DNSingleton
View 类
        public static function Show($view,$data=array(),$use_wrapper=true)

        public static function return_json($ret)
        返回 json 数据，自带 exit

        public static function return_redirect($url)
        跳转结束，自带 exit

        public static function return_route_to($url)
        跳转到 DnRoute::URL 自带 exit;——这是唯一破坏耦合性的函数

        public function _Show($view,$data=array(),$use_wrapper=true)
        显示数据，第一个为不带 .php 结尾的 view 文件，第二个为传递过去的数据，第三个参数是是否使用页眉页脚

        public function init($path)
        初始化， view 的路径

        public function setBeforeShow($callback)
        设置在显示前的回调，在 DNMVCS 类中，设置成开始输出前关闭 mysql

        public function showBlock($view,$data)
        显示一小块 view，用于调试

        public function _assign($key,$value)
        设置 key-value 模式的数据，不推荐使用，你应该传入整个数组

	public function setWrapper($head_file,$foot_file)
        设置页眉页脚

```
### DNConfig 配置类
```
class DNConfig extends DNSingleton
配置类
        public static function Setting($key)
        读取 设置, 不用 set 是避免和 get 对称

        public static function Get($key,$file_basename='config')
        获取配置

        public static function Load($file_basename)
        加载配置文件

        public function init($path,$path_common=null)
        初始化

        public function _Setting($key)
        setting 的实现函数

        public function _Get($key,$file_basename='config')
        get 的实现函数

        public function _Load($file_basename='config')
        load  的实现函数

```
### DNException 异常处理类
```
异常处理。
class DNException extends Exception
        public static function ThrowOn($flag,$message,$code=0)
        如果 $flag为真，则抛出异常。 用于减少 if 语句
        如 MyException::ThrowOn(true,"test",-110);
        等价于 if(true){throw new MyException("test",-110);}

        public static function SetDefaultAllExceptionHandel($callback)
        公用，用于设置默认的异常

        public static function HandelAllException()
        接管异常

        public static function SetSpecial($class,$callback)
        为特定异常设置错误处理方法，通常用于控制器初始化里对单一类型异常处理

        public static function ManageException($ex)
        给扩展类默认的异常方法

        public static function SetErrorHandel($error_handel)
        设置错误 

        public static function OnException($ex)
        默认异常，扩展类里重载这个静态方法以实现自己的异常处理方式
```
### DNDB 数据库类
```

## 还有什么要说的

使用它，鼓励我，让我有写下去的动力