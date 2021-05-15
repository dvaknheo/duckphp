# 一般流程
[toc]
## 开发人员角色

DuckPhp 的使用者角色分为 `业务工程师`和`核心工程师`两种。

`业务工程师`负责日常 Curd 。作为`业务工程师`， 你不能引入 DuckPhp 的任何东西，就当 DuckPhp 命名空间里的东西不存在。

`核心工程师`才去研究 DuckPhp 类里的东西。做大家统一的底层代码。

## nginx 配置
这是我的 nginx 配置，如果在安装时候，欢迎反馈。
毕竟一般配置好后都不会去动。出现什么安装问题会没特别在意


```
server {
    root DUCKPHP_ROOT/template/public;
    index index.php index.html index.htm;
    
    try_files $uri $uri/ /index.php$request_uri;
    location ~ \.php {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_split_path_info ^(.*\.php)(/.*)?$;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 目录结构

DuckPhp 代码里的 template 目录就是我们的工程目录示例。也是工程桩代码。

在执行 `./vendor/bin/duckphp new` 的时候，会把代码复制到工程目录。 并做一些改动。
@script 目录结构

```text
+---app                         // psr-4 标准的自动加载目录。
|   +---Business                // 业务目录
|           BaseBusiness.php    // 服务基类
|   |       TestBusiness.php    // 测试 Business
|   +---Controller              // 控制器目录
|           BaseController.php  // 控制器基类
|   |       Main.php            // 默认控制器
|   +---Model                   // 模型放在里
|           BaseModel.php       // 模型基类
|   |       TestModel.php       // 测试模型
|   +---Helper                  // 助手类目录
|   |       BusinessHelper.php  // 服务助手类
|   |       ControllerHelper.php// 控制器助手类
|   |       ModelHelper.php     // 模型助手类
|   \---System                  // 基类放在这里
|           App.php             // 默认框架入口文件
|           BaseException.php   // 系统错误基类
+---config                      // 配置文件放这里
|       config.php              // 配置，目前是空数组
|       setting.sample.php      // 设置，去除敏感信息的模板
+---view                        // 视图文件放这里，可调
|   |   main.php                // 视图文件
|   \---_sys                    // 系统错误视图文件放这里
|           error-404.php       // 404 页面
|           error-500.php       // 500 页面
+---public                      // 网站目录
|       index.php               // 主页，入口页
\---duckphp-project             // 命令行入口
```
这个目录结构里，`业务工程师`只能写 `app/Controller`,`app/Model`,`app/Business,`view` 这四个目录。
有时候需要去读 `app/Helper` 目录下的的类。其他则是`核心工程师`的活。

app 目录，就是放 LazyToChange 命名空间的东西了。 app 目录可以在选项里设置成其他名字
命名空间 LazyToChange 是 可调的。比如调整成 MyProject ,TheBigOneProject  等。
可以用 `./vendor/bin/duckphp new --namespace TheBigOneProject` 调整。

文件都不复杂。基本都是空类或空继承类，便于不同处理。
这些结构能精简么？
可以，你可以一个目录都不要。

`System/App.php` 这个文件的入口类继承 `DuckPhp\DuckPhp` 类，工程的入口流程会在这里进行，这里是`核心工程师`重点了解的类。

BaseController, BaseModel, BaseBusiness 是你自己要改的基类，基本只实现了单例模式。

Helper 目录，助手类，如果你一个人偷懒，直接用 APP 类也行  


### 总结如何精简目录
* 移除 app/Helper/ 目录,如果你直接用 App::* 替代助手类。
* 移除 app/System/BaseController.php 如果你的 Controller 和默认的一样不需要基本类。
* 移除 app/System/BaseModel.php 如果你的 Model 用的全静态方法。
* 移除 app/System/BaseBusiness.php 如果你的 Business 不需要 G() 可变单例方法。
* 移除 duckphp-project 如果你使用外部 http 服务器
* 移除 config/ 目录,在启动选项里加 'skip_setting_file'=>true ，如果你不需要 config/setting.php，
    并有自己的配置方案
* 移除 view/\_sys  目录 你需要设置启动选项里 'error\_404','error\_500,'error_debug‘’。
* 移除 view 目录如果你不需要 view ，如 API 项目。
* 移除 TestBusiness.php ， TestModel.php  测试用的东西
@script 目录结构

----


## 工程完整架构图


对应上面的文件结构，你的工程应该是这么架构。

![arch_full](arch_full.gv.svg)

文字版
```text
           /-> View-->ViewHelper
Controller --> Business ------------------------------ ---> Model
         \         \   \               \  /                  \
          \         \   \-> (Business)Lib ----> ExModel----------->ModelHelper
           \         \             \                
            \         ---------------->BusinessHelper
             \-->ControllerHelper
```

同级之间的东西不能相互调用

* 写 Model 你可能要引入 MyProject\Helper\ModelHelper 助手类别名为 M 。
* 写 Business 你可能要引入 MyProject\Helper\BusinessHelper 助手类别名为 B 。
* 写 Controller 你可能要引入 MyProject\Helper\ControllerHelper 助手类别名为 C 。
* 写 View 你可能要引入 MyProject\Helper\ViewHelper 助手类别名为 V 。
* 不能交叉引入其他层级的助手类。如果需要交叉，那么你就是错的。
* 小工程可以用直接使用入口类 MY\Base\App 类，这包含了上述类的公用方法。
* ContrllorHelper,ModelHelper,BusinessHelper,ViewHelper 如果你一个人偷懒，直接用 APP 类也行  
* Business 按业务逻辑走， Model 按数据库表名走
* Lib 其实是特殊的 Business 用于其他 Business 调用
* ExModel 是特殊 Model 表示多个表混合调用。
* 图上没显示特殊的 AppHelper

##  教程索引

助手类教程在这里 [助手类教程](tutorial-helper.md)，基本上，看完助手类教程，`业务工程师`就可以开干了。

此外有什么不了解的，问`核心工程师`吧。
比如路由方面，常见是文件路由。 [路由教程](tutorial-route.md)

如果你的项目使用内置数据库，或许你还要看  [数据库教程](tutorial-helper.md)

还有 [异常处理](tutorial-exception.md) 异常处理，和 [事件处理](tutorial-event.md)

命令行怎么处理，需要看  [命令行教程](tutorial-console.md)

一些额外功能，你要看   [内置扩展介绍](tutorial-extension.md)

使用第三方插件或把项目变成插件 需要看 [插件模式](tutorial-plugin.md)

最后，查看 [开发相关](tutorial-support.md) 加入开发

## 入口文件和选项

### Web 的入口文件

和很多 Web 框架一样，我们的工程是从 public/index.php 开始的
@script File: `template/public/index.php`

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');    //@DUCKPHP_HEADFILE

echo "<div>You should not run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行模板文件，建议用安装模式 </div>\n";              //@DUCKPHP_DELETE

// 设置工程命名空间对应的目录，但强烈推荐修改 composer.json 使用 composer 加载 
if (!class_exists(\LazyToChange\System\App::class)) {
    \DuckPhp\DuckPhp::assignPathNamespace(__DIR__ . '/../app', "LazyToChange\\"); 
    \DuckPhp\DuckPhp::runAutoLoader();
}

$options = [
    // 这里可以添加更多选项
];
//*/
\LazyToChange\System\App::RunQuickly($options);
//*/

/* //等价于
$options['override_class'] = LazyToChange\System\App::class,
\DuckPhp\DuckPhp::RunQuickly($options);
//*/
```
入口类前面部分是处理头文件的。

带入工程文件目录 $path ，和工程命名空间 $namespae。

然后就这句话

```php
\DuckPhp\DuckPhp::RunQuickly($options);
```
RunQuickly 相当于 `\DuckPhp\DuckPhp::G()->init($options,function(){})->run(); `
 会执行根据选项，返回  `LazyToChange\System\App`

###  工程入口文件

所以我们现在来看 `app/System/App.php` 对应的 LazyToChange\System\App 类就是入口了。

@script File: `template/app/System/App.php`

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    //@override
    public $options = [
        'is_debug' => true,        
        // 'use_setting_file' => true,
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        
        //'path_info_compact_enable' => false,        
    ];
    /**
     * console command sample
     */
    public function command_hello()
    {
        // 多一个 hello 的命令
        echo "override you the routes\n";
    }
    //@override
    protected function onPrepare()
    {
        //your code here
    }
    //@override
    protected function onInit()
    {
        // your code here
    }
    //@override
    protected function onRun()
    {
        // your code here
    }
    public function __construct()
    {
        parent::__construct();
        $options = [];

        // @autogen by tests/genoptions.php
// 【省略选项注释】
        // @autogen end
        
        $this->options = array_replace_recursive($this->options, $options);
    }
}

```
这里的代码省略了一大堆注释，这些注释选项，都是默认选项。和打开的效果是一样的。

 //@override 注释都是用于重写的。

在构造父方法里，我们合并了一大堆注释的选项以做不同选择。

后面我们开始解释这些代码。

### // @DUCKPHP 开始的注解
 我们是看 template 文件夹看到一些  // @DUCKPHP 开始的注解。在安装脚本运行之后，实际这些注解的行会有特殊变动。
共有4个注解

+ // @DUCKPHP_DELETE 模板引入后删除
+ // @DUCKPHP_HEADFILE 头文件调整

### 关于选项

术语 `选项`和 `设置`， `配置` 相区分如下：

- 选项 ，传递给入口类的内容
- 配置，可有可无的配置文件。
- 设置，配置的 setting 文件，敏感信息

index.php 中的 $options , app/Base/App.php 的 在初始化的时候都会传递合并入入口类的 $options 公开属性里。
在 App 类的代码里，还留有一大堆排序后的注释选项。打开后也合并如 options 公开属性。
这些注释选项代码和默认的是一致的。

DuckPhp 只要更改选项就能实现很多强大的功能变化。
如果这些选项都不能满足你，那就启用扩展吧，这样有更多的选项能用。
如果连这都不行，那么，就自己写扩展吧。

参考 [参考索引页的选项部分](ref/index.md) 获得所有选项信息


### 基本选项详解

DuckPhp 的示例文件，注释的都是默认选项，没使用默认注释选项的，这里说明一下。

'is_debug'=>false,

    配置是否在调试状态。
'platform'=>'',

    配置开发平台 * 设置文件的  platform 会覆盖
error_* 选项为 null 用默认，为 callable 是回调，为string 则是调用视图。

'error_debug'=>'_sys/error-debug',

    is_debug 打开情况下，显示 Notice 错误
'error_404'=>'_sys/error-404'

    404 页面
'error_500'=>'_sys/error-500'

    500 页面，异常页面都会在这里


## 请求流程和生命周期

怎么就从 DuckPhp\DuckPhp 切到 LazyToChange\System\App 类了？

index.php 就只执行了

DuckPhp\DuckPhp::RunQuickly($options, $callback) 

发生了什么

等价于 DuckPhp\DuckPhp::G()->init($options)->run();

init 为初始化阶段 ，run 为运行阶段。$callback 在init() 之后执行

#### init 初始化阶段

    处理是否是插件模式
    处理自动加载  AutoLoader::G()->init($options, $this)->run();
    处理异常管理 ExceptionManager::G()->init($exception_options, $this)->run();
    checkOverride() 检测如果有覆盖类，切入覆盖类（LazyToChange\System\App）继续 
    接下来是 initAfterOverride;

#### initAfterOverride 初始化阶段

    调整选项 initOptions()
    调整外界 initContext()
    调用用于重写的 onPrepare(); 
    初始化默认组件 initDefaultComponenents()
    加入扩展 initExtends()
    调用用于重写的  onInit();

#### run() 运行阶段

    处理 setBeforeRunHandler() 引入的 beforeRunHandlers
    异常准备
        beforeRun()；
            重制 RuntimeState 并设置为开始
            绑定路由
        * onRun ，可 override 处理这里了。
        ** 开始路由处理 Route::G()->run();
        如果返回 404 则 On404() 处理 404
    如果发生异常
        进入异常流程
    清理流程
#### clear 清理
只有一个动作： 设置 RuntimeState 为结束

## 重写入口类
### 请求流程中添加你的代码


属性 options_project 的数据会合并入 $this->options 。工程额外选项请在这里添加

+ protected function onPrepare() 
	用于替换默认组件等。

+ protected function onInit() 
	在初始化结束之后执行。要在初始化完成后做额外工作就在这里加了。

+ protected function onRun()
	运行阶段执行。

核心工程师重写这三个方法，就能给你的工程带来多种多样的变化了。

### 接管替换默认实现

你可以在 onPrepare() 方法里替换默认的实现。
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

为了 onInit 使用方便

* 为什么 Core 里面的都是 App::Foo(); 而 Ext 里面的都是 App::G()::Foo();
因为 Core 里的扩展都是在 DuckPhp\Core\App 下的。

Core 下面的扩展不会单独拿出来用。如果你扩展了该方面的类，最好也是让用户通过 App 或者 MVCSA 助手类来使用他们。


接下来是[路由](tutorial-route.md)这一章教程，  Route::G()->run() 的具体内容

### 加载扩展

DuckPhp 扩展的加载是通过选项里添加，$options['ext']数组实现的

    扩展映射 ,$ext_class => $options 。
    
    $ext_class 为扩展的类名，如果找不到扩展类则不启用。
    $ext_class 满足组件接口。在初始化的时候会被调用。
    $ext_class->init(array $options,$context=null); // context 为 DuckPhp 的实现类。
    
    如果 $options 为 false 则不启用，
    如果 $options 为 true ，则会把当前全局 $options 传递进去。
    如果 $options 为 string 则会映射到全局 $options 的键为 $optoins 的值

## 问答

+ Helper 类为什么要在 Helper 目录下，

  原因，配合 cloneHelper 用。

+ 为什么会有个“我觉得恶心的”G() 单字母静态方法

  你可以把 ::G()-> 看成和 facades 类似的门面方法。
  可变单例是 DuckPhp 的核心。
  你如果引入第三方包的时候，不满意默认实现，可以通过可变单例来替换他

  var_dump(MyClass::G()); 使用 Facades 就没法做到这个功能。

+ 为什么不直接用 DB 类，而是用 DbManager
  做日志之类的处理用

+ 为什么名字要以 *Model *Business 结尾
  让单词独一无二，便于搜索

+ 为什么是 Db 而不是 DB 。
  为了统一起来。  缩写都驼峰而非全大写

+ 回调 Class::Method Class@Method Class->Method 的区别

  ->  表示 (new Class)->Method
  @ 表示 Class::G()->Method
  :: 表示 Class::Method
  ~ 前缀扩充到当前命名空间

+ 门面， DuckPhp 用可变单例代替了门面
  中间件， DuckPhp 提供了简单的中间件扩展 MyMiddlewareManager，但不建议使用。

+ 事件

  见事件这一篇

请求和响应， DuckPhp 没这个概念
但在 控制器助手类里有很多相同的行为

数据库 ，DuckPhp 的数据库没那么强大

模型 


视图 DuckPhp 的视图原则

错误处理
日志
验证， duckphp 没验证处理，你需要第三方类

缓存 duckphp 默认是空缓存类

Session // 用 App::SESSION(); 

Cookie // 用 COOKIE

多语言 _\_l ,_\_h

上传 // 没

命令行 , 见命令行这一篇

扩展库,见教程



