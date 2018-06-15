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
DNMVCS Notice: no setting file!,change setting.sample.php to setting.php !
### 后续的工作
新建工程怎么做？ 复制 sample 目录到你工程目录就行，修改 index.php ，使得引入正确的库

还有哪些没检查的？ 服务器配置 path_info 对了没有。 数据库也没配置和检查。
想要更多东西，可以检出  dnmvcs-full 这个工程，里面有全部的测试
开始学习吧

### 目录结构
工程的目录结构入夏
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
## 简单入门

深入的级别
1. 使用默认配置实现目的 
2. 只改配置实现目的
3. 继承接管特定类实现目的
4. 魔改。

## 配置

```php
[
    'namespace'=>'MY',  // 默认的命名空间
    'enable_simple_mode'=>true, //启用无命名空间模式
    'enable_paramters'=>true,   //启用 paramter 切分
    'fullpath_config_common'=>'',  // 共享配置的目录
    'fullpath_framework_common'=>'', 
    //无命名空间下， CommonModel,CommonService 后缀的绝对加载路径用于多网站配合的情况。
];
[]
    //--以下不建议变更
    'path_namespace'=>'app', // 默认 psr-4 加载目录。一般不动。
    'path_autoload'=>'classes', //默认加载无命名空间的类。
    'path_framework_simple'=>'app', //无命名空间情况下 Model,Service 后缀的类加载路径，两个下划线将会转为目录 一般和有命名空间的混一起，。

    'path_config'=>'config',  // 默认配置目录

    'path_controller'=>'app/Controller', // 控制器目录
    'namespace_subcontroller'=>'Controller', //控制器的子命名空间。
    'path_controller_simple'=>'app/Controller', //无命名空间模式下控制器目录。

    'path_view'=>'view',  // view 目录
            
];
```

## 核心函数
这里的方法是入口函数。很初级的地方。

G 函数，单例模式。后面再详细说明。
autoload ，自动加载。
init 初始化 配置一大堆。默认配置如下
```
//省略
```

##

run 开始使用路由。
##
```
static G($object=null)   
    G 单例函数是整个系统最有趣的地方。

static RunQuickly($path='')
    DNMVCS::RunQuickly () 相当于 DNMVCS::G()->init()->run();结束。
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

```

Show($data=array(),$view=null)
    显示视图,为什么数据放前面， get_defined_vars(); 后面留空
    * 高级开发者注意，这里的 $view 为空是在主类里处理的，子类化需要注意 *
DB()
    数据库 DNManager 里配置的
    返回数据库
DB_W()
    返回写入的数据
DB_R()
    读取用的数据库 和默认配置一样。
URL($url=null)
    获得某路由的正确显示方式
Param()
    //获得路径切片
Setting($key)
    //读取设置
GetConfig($key)
    // 读取配置
LoadConfig($file_basename)
    //
ExitJson($ret)

ExitRedirect($url,$only_in_site=true)

ExitRouteTo($url,$only_in_site=true)

```
## 非静态方法
这里的方法偶尔会用到，所以没静态化 。
在 controller 的构建函数，你可能会用到。

```

assignRoute($key,$value=null)
给路由加回调。

setViewWrapper($head_file=null,$foot_file=null)
给输出 view 加页眉页脚
assignViewData($key,$value=null)
给 view 加数据，不推荐用这个函数
showBlock($view,$data)
展示一块 view ，不是
assignExceptionHandel($classes,$callback=null)
分配异常回调。
isDev
判断是否在开发状态。

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
## 辅助方法
常用方法的缩写
```
H($str) html 编码 
Import($file) 手动导入默认lib 函数


TODO recordset_h($data,$cols) 给一排 sql 数组 html 编码
TODO recordset_url($data,$cols_map) 给一排 sql 返回数组 加url
```
# 进一步扩展
## G 方法
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
*但是静态方法不替换，请注意这一点。 DNMVSC::View 和 DNView 的差异注意一下 *

为什么不是 GetInstance ? 因为太长，这个方法太经常用。

## 异常的快速处理

使用 trait DNThrowQuickly
```
MyException::ThrowOn($flag,$message,$code);
```
## 类的分类
DNMVCS 主类里一些函数，是调用其他类的实现。

DNAutoLoad 加载类

DNRoute 路由类
DNView 视图类
DNConfig 配置类
DNDBManger 数据库管理类
DNDB 简单实现的一个数据库类。封装了 PD 和 Medoo 兼容，也少了 Medoo 的很多功能。 
DNMedoo 这个类需要手动调用，在另外一个文件，是 Medoo 的一个简单扩展，和 DNDB 接口一致。

DNExceptionManager 异常管理类
这个类都是静态方法，基本上没人会接管这个类吧。要不覆盖 DNMVCS 的 init 方法呗

## 奇淫巧技
DNMVCSEx 里有几个方法是实验性的

1. 简单的实现 api 接口。
2. 不同的类参数，实现同一调用
3. 我想修改 G 函数，让 DB 只能被 Model , ExModel 调用。Model 只能被 ExModel(?) ,Service 调用 。 LibService 只能被Service 调用  Service只能被 Controller 调用
