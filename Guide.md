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

还有哪些没检查的？ 服务器配置 path_info 对了没有。 数据库也没配置和检查。
想要更多东西，可以检出  dnmvcs-full 这个工程，里面有全部的测试样例。

开始学习吧

### 目录结构
工程的目录结构
```
+---app // psr-4 标准的自动加载目录
|   +---Controller  // 路由控制器
|   |       Main.php    // 默认入口文件
|   +---Model
|   |       TestModel.php   // 测试 Model 
|   \---Service
|           TestService.php //测试 Service
+---classes
|       ForAutoLoad.php // 测试自动加载
+---config
|       config.php  // 配置，目前是空数组
|       setting.php // 设置，敏感文件，不放在版本管理里
|       setting.sample.php  // 设置，对比来
+---lib
|       ForImport.php //用于测试导入文件
+---view
|   |   main.php  // 视图文件
|   \---_sys
|           error-404.php  // 404 
|           error-500.php  // 500 出错了
|           error-debug.php // 调试的时候显示的视图
|           error-exception.php // 出异常了，和 500 不同是 这里是未处理的异常。
\---www
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
1. 使用默认配置实现目的 
2. 只改配置实现目的
3. 继承接管特定类实现目的
4. 魔改。

## 设置
init([]) 方法的参数是可配置的，默认设置如下
默认设置
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
** 一些高级配置，用于魔改的请自己去翻看代码。 *
启用无命名空间模式 ，就是不想写那么多带命名空间的代码， *Service,  *Model 这样结尾的类直接自动加载

工程的设置文件是样例 setting.sample.php 。

```php
$data=array();
$data['is_dev']=true;
$data['db']=array(
	'dsn'=>'mysql:host=???;port=???;dbname=???;charset=utf8;',
	'user'=>'???',
	'password'=>'???',
);
return $data;
```
只有一个设置项目 is_dev 用于 判断是否是开发状态，默认并没使用到。
db ，配置数据库。
db_r， 配置读写分离的数据库
medoo ，配置 medoo 数据库，见 和 medoo 配合这一步骤讲解。
medoo_r 配置 medoo 只读数据库
## 开始自己的代码
以 /about/foo 为例，使用无命名空间模式
首先我们要写相关控制器
```php
<?php
// app/Controller/about.php
class DNController
{
    public function foo()
    {
        echo MiscService::G()->foo();
    }
}
```
在控制器里，我们调用了 MiscService 这个服务
MiscService 调用 MiscModel 的实现。此外，我们要调整 返回值的内容
我们用 DNSingleton单例，避免 new .
```php
<?php
// app/Service/MiscService.php
class MiscService
{
    use \DNMVCS\DNSingleton;
    public function foo()
    {
        //log something.
        $time=MiscModel::G()->getTime();
        $ret='Now is'.$time;
        return $ret;
    }
}
```
完成 MiscModel
```php
// app/Model/MiscModel
class MiscService
{
    use \DNMVCS\DNSingleton;
    public function getTime()
    {
        return DATE(DATE_ATOM);
    }
}
```
这就是 DNMVC 下的简单流程了。其他开发类似
## 理解路由和控制器
DNMVCS 的控制器有点像CI，不需要继承什么，就这么简单。
甚至连名字都不用，用默认的 DNController 就够了。
而且支持子命名空间多级目录。如果开启简单模式，也可用 __代替 \ 切分。
DNController 重名了怎么办，比如我要相互引用？ 
1 那是你不应该这么做，2 你也可以采取名称对应的啊。

DNMVCS 还支持路由映射。 
正则用 ~
要指定 GET/POST 在最前面加http 方法.

DNMVCS 支持 Paramter，你可以在设置里关掉。
Parameter 切片会直接传递进 方法里作为参数
路由表里，用正则切分会传递进方法，不管是否开启 enable_paramters

如果你想加其他功能，可以继承 DNRoute 类。 
比如 路由不用 path_info 用 $_GET['_r'] 等，很简单的。 DNSimpleRoute 库已经实现。

路由这块很多东西，300 行代码不是这么简单描述的
## 重写 错误页面
错误页面在 view/_sys 里。你可以修改相应的错误页面方法。
比如 404 是 view/404.php
高级一点，你可以 扩展 DNMVCS 的主类实现

##
run 开始使用路由。 如果你不想要路由。只想要特定结构的目录， 不调用 run 就可以了。
比如我一个样例，只想要 db 类等等。

# DNMVCS 主类
## 基本方法
```
static G($object=null)   
    G 单例函数是整个系统最有趣的地方。
    传入 $object 将替代默认的单例。
	比 PHP-DI简洁
static RunQuickly($options)
    DNMVCS::RunQuickly () 相当于 DNMVCS::G()->init()->run();结束。
	默认配置 framework_class \\MY\\Framework\\Main 如果有这个类，就替换这个类进行。
autoload($path,$options=array())
    自动加载。处理自动加载机制。 得找到自动加载才把子类化的文件载入进来，所以这个方法单列出来。
init($options=[]]) 
    初始化，这是最经常子类化完成自己功能的方法。我已经尽量简化简化了。
    如果在初始化之前没有 autoload 会在这里执行，如果已经执行了 autoload 会把 默认配置合并 autoload 的配置以及参数的配置作为配置使用。
run()
    开始路由，执行。这个方法拆分出来是为了。不想要路由，只是为了加载一些类的需求的。
##
```

##

## 常用静态方法方法
这些方法因为太常用，所以静态化了。
包括 视图view,路由，数据库，配置 ，
```

Show($data=array(),$view=null)
    显示视图 实质调用 DNView::G()->_Show();
    为什么数据放前面， get_defined_vars(); 后面留空  如 DN::Show(get_defined_vars());把当前 controller 的view拿下来。
    * 高级开发者注意，这里的 $view 为空是在静态方法里处理的，子类化需要注意 *
DB()
    返回数据库,实质调用 DBManager::G()->_DB();
    数据库管理类 DNManager 里配置的
DB_W()
    返回写入的数据 实质调用 DBManager::G()->_DB();
    默认和 DB() 函数一样
DB_R()
    读取用的数据库 和默认配置一样。
URL($url=null)
    调用 DNRoute::G()->_URL();
    获得某路由的正确显示方式
    当你重写 DNRoute 类后，你可能需要重写这个方法来展示
Param()
    //获得路径切片 
Setting($key)
    //读取设置
GetConfig($key)
    // 读取配置 配置放在 config/config.php 里
LoadConfig($file_basename)
    // 加载其他配置，
ExitJson($ret)
    // 打印 json_encode($ret) 并且退出
ExitRedirect($url)
    // header 跳转退出
ExitRouteTo($url)
    // header 跳转到 URL($url);
ThrowOn($flag,$message,$code);
    如果 flag 成立则抛出 DNException 异常。 调用 DNException::ThrowOn
    如果是你自己的异常类 ，可以 use DNThrowQuickly 实现 ThrowOn 静态方法。

H($str)
    html 编码 这个函数常用，所以缩写。
Import($file)
    手动导入默认lib 目录下的包含文件 函数
ImportSys($file)
    手动导DNMVCS目录下的包含文件 函数。DNMVCS库目录默认不包含其他非必要的文件
	因为需求不常用，所以没自动加载
	比如在调试状态下的奇淫巧技：限定各 G 函数的调用。
	
TODO 代码未完成 recordset_h($data,$cols) 给一排 sql 数组 html 编码
TODO 代码未完成  recordset_url($data,$cols_map) 给一排 sql 返回数组 加url

```

## 非静态方法
这里的方法偶尔会用到，所以没静态化 。
在 controller 的构建函数，你可能会用到。
assign 系列函数，都有两个模式 func($map)，和 func($key,$value) 模式
方便大量导入。


```
assignRoute($key,$value=null)
    给路由加回调。
setViewWrapper($head_file=null,$foot_file=null)
    给输出 view 加页眉页脚
assignViewData($key,$value=null)
    给 view 加数据，不推荐用这个函数
showBlock($view,$data)
    展示一块 view ，用于调试的场合
assignException($classes,$callback=null)
    分配异常回调。
setDefaultExceptionHandel()
    接管默认的异常处理
isDev
实际读设置里的，判断是否在开发状态。

```
## 事件方法
实现了默认事件回调的方法。扩展以展现不同事件的显示。

```
onBeforeShow()
    在输出 view 开始前处理，默认只是关闭数据库。
onShow404()
    404 回调。这里没传各种参数，需要的时候从外部获取。
onException($ex)
    发生未处理异常的处理函数。
onErrorException($ex)
    处理错误，显示500错误。
onDebugError($errno, $errstr, $errfile, $errline)
    处理 Notice 错误。
onErrorHandel($errno, $errstr, $errfile, $errline)
    处理 PHP 错误
```

# 进一步扩展
## 子类化和 G 方法
G 函数，单例模式。
来自 trait DNSingleton

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
注意 * 但是静态方法不替换，请注意这一点。 DNMVSC::Show() 和 DNView::Show) 的差异注意一下 *

为什么不是 GetInstance ? 因为太长，这个方法太经常用。
所以你可以扩展各种内部类以实现不同功能。
比如你要自己的路由方法在 autoload 类后，init 里。

DNRoute::G(MYRoute::G());
这样 MYRoute 就接管了 DNRoute 了。

DNView::G(AdminView::G());
这样 AdminView 就接管了 DNView 了。

## 类的分类
DNMVCS 主类里一些函数，是调用其他类的实现。基本都可以用 G 方法替换

- DNAutoLoad 加载类
- DNRoute 路由类
- DNView 视图类
- DNConfig 配置类
- DNDBManger 数据库管理类
- DNExceptionManager 异常管理类 
    异常管理类都是静态方法，基本上没人会接管这个类吧。要不覆盖 DNMVCS 的 init 方法呗

- DNDB 简单实现的一个数据库类。封装了 PD 和 Medoo 兼容，也少了 Medoo 的很多功能。 
- DNMedoo 这个类需要手动调用，在另外一个文件，是 Medoo 的一个简单扩展，和 DNDB 接口一致。

## 异常的快速处理
使用 trait DNThrowQuickly
```
MyException::ThrowOn($flag,$message,$code);
```
等价于下面，少写了好多东西
```
if($flag){throw new MyException($message,$code);}
```

注意到这会使得 debug_backtrace 调用堆栈不同。
## 和 Medoo 配合
DNMedoo.php 就是用 Medoo 代替默认的 DNDB 类。
但是用 DNMedoo 前你要手动添加 Medoo 的引用和手动引用 DNMedoo.php
然后 DNDBManager::G(MedooDBManager::G());
DNMedoo 类的除了默认的 Medoo 方法，还扩展了几个方法

## 奇淫巧技
DNMVCSEx 里有几个方法是实验性的

1. 简单的实现 api 接口。 
2. 利用反射不同的类参数，实现同一调用
3. 我想修改 G 函数，让 DB 只能被 Model , ExModel 调用。Model 只能被 ExModel,Service 调用 。 LibService 只能被Service 调用  Service只能被 Controller 调用
 系统里的 DNDebugSingleton 已经实现。详细可以自己去子类化

# 扩展你的类
```
DNAutoLoad 加载类
DNAutoLoad 不建议扩展。因为你要有新类进来才有能处理加载关系，不如自己再加个加载类呢。

DNConfig 配置类
    DNConfig 类获得配置设置
DNView 视图类
    $this->isDev
DNDBManger 数据库管理类
    这个也许会经常改动。比如用自己公司的 DB 类，要在这里做一个封装。
DNExceptionManager 异常管理类
    这个不建议改
DNRoute 路由类
    这应该会被扩展,加上权限判断等设置
DNException 异常类
    你自己的异常类应该 use  DNThrowQuickly 没必要继承 DNException
```