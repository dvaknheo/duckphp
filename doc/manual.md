# DNMVCS - 另一个手册
## 序言
本手册以 ThinkPHP 6.0 完全开发手册为基准，按功能而非按函数来重新编排。

一个东西看起来像鸭子，走起来像鸭子，而且叫起来都像鸭子，那它就一定是只鸭子。

看代码不怕没注释，最头疼的是跳来跳去的关系。 就怕你不知道的角落里有东西。
开箱即用，不用配置。
### 安装

### 开发规范

Serivce 类 要加 Service 类后缀
Model 类 加 Model 类后缀
类推。
原因是不用看 use 区块 也能了解这些类是什么的，不会混乱。

### 目录结构


工程的桩代码,完整的默认目录结构

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
|           error-exception.php // 异常页面
\---public                  // 网站目录
        index.php           // 主页，入口页
```

文件都不复杂。基本都是空类或空继承类，便于不同处理。

这些结构能精简么？可以，你可以一个目录都不要。

BaseController, BaseModel, BaseService 是你自己要改的基类，基本只实现了单例模式。

ContrllorHelper,ModelHelper,ServiceHelper 如果你一个人偷懒，直接用 APP 类也行  

如何精简目录

* 移除 app/Base/Helper/ 如果你直接用 App::* 替代 M,V,C,S 助手类。
* 移除 app/Base/BaseController.php 如果你的 Controller 和默认的一样不需要基本类。
* 移除 app/Base/BaseModel.php 如果你的 Model 用的全静态方法。
* 移除 app/Base/BaseService.php 如果你的 Service 不需要 G 方法。
* 移除 bin/start_server.php 如果你使用外部 http 服务器
* 移除 config/ 在启动选项里加 'skip_setting_file'=>true ，如果你不需要 config/setting.php，
    并有自己的配置方案
* 移除 view/\_sys  你需要设置启动选项里 'error\_404','error\_500','error\_exception' 。
* 移除 view 如果你不需要 view ，如 api。
* 移除 TestService.php ， TestModel.php  测试用的东西

### 配置
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
    'reload_for_flags'=>true,   // 从设置文件里重新加载 is_debug,platform 选项
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

其他组件的配置，也可能写在这里。

##### 基本选项
'skip_setting_file'=> false,

    新手之一最容易犯的错就是，没把这项设置为 true.
    这个选项的作用是跳过读取 setting.php  敏感文件。
    为什么要这么设置， 防止传代码上去而没传设置文件。
    造成后面的错误。
'path'=>null,

    基本路径，其他配置会用到这个基本路径。
'namespace' =>'MY',

    工程的 autoload 的命名空间，和很多框架限定只能用 App 作为命名空间不同，DNMVCS 允许你用不同的命名空间
'path_namespace'=>'app',

    默认的 psr-4 的工程路径配合 skip_app_autoload  使用。
'skip_app_autoload'=>false

    跳过应用的加载，如果你使用composer.json 来加载你的工程命名空间，你可以打开这个选项。
'override_class'=>'Base\App',

**重要选项**

    基于 namespace ,如果这个选项的类存在，则在init()的时候会切换到这个类完成后续初始化，并返回这个类的实例。
    注意到 app/Base/App.php 这个文件的类 MY\Base\App extends DNMVCS\DNMVCS;
    如果以  \ 开头则是绝对 命名空间
'is_debug'=>false,

    配置是否在调试状态。
'platform'=>'',

    配置开发平台 * 设置文件的  platform 会覆盖
'skip_view_notice_error'=>true,

    view 视图里忽略 notice 错误。
'reload_for_flags'=>true,

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
'error_exception'=>'_sys/error-exception'

    excption 页面。

##### "ext" 选项和扩展

ext 是一个选项，这里单独成一节是因为这个选项很重要。涉及到 DNMVCS 的扩展系统

在 DNMVCS/Core 里， ext 是个空数组。

    扩展映射 ,$ext_class => $options。

    $ext_class 为扩展的类名，如果找不到扩展类则不启用。
    
    $ext_class 满足接口。
    $ext_class->init(array $options,$context=null);
    
    如果 $options 为  false 则不启用，
    如果 $options 为 true ，则会把当前 $options 传递进去。




## 架构

### 请求流程

DNMVCS::RunQuickly($options) 发生了什么

DNMVCS::G()->init($options)->run();

    init 为初始化阶段 ，run 为运行阶段。
    init 阶段做的事情如下
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

### 架构总览

### 入口文件
我们看入口类文件 public/index.php

```php
<?php
require(__DIR__.'/../vendor/autoload.php');

$path=realpath(__DIR__.'/..');
$options=[];

$options['path']=$path;
$options['namespace']='MY';
\DNMVCS\DNMVCS::RunQuickly($options, function () {
});
// \DNMVCS\DNMVCS::G()->init($options)->run();
// var_export(\DNMVCS\DNMVCS::G()->options);
```
入口类前面部分是处理头文件的。
然后处理直接 copy 代码提示，不要直接运行。
起作用的主要就这句话
```php
\DNMVCS\DNMVCS::RunQuickly($options, function () {
});
```
相当于后面调用的 \DNMVCS\DNMVCS::G()->init($options)->run(); 第二个参数的回调用于 init 之后执行。

init, run 分两步走的模式。

最后留了 dump 选项的语句。

接下来我们看 $options 里可以选什么
### URL 访问

### 可变单例和G函数
### 扩展
## 路由
### 基础路由
DNMVCS 默认支持文件型路由。
对应控制器就是  Controller 下的文件。
支持子文件夹作为子命名空间。
### 扩展路由
DNMVCS 相比 Core 默认加载了 RouteHookRewrite 和  RouteHookRouteMap。

### 路由钩子
你可以自己重载 Route 类以实现默认的路由
一般来说，不要修改底层路由类。而是通过修改路由类的选项达到目的。
如果修改路由类没法达到你的目的，那么就接着上钩子。
App::G()->addRouteHook($callback,$position, $once=true);
$position 有四个
$callback($path_info) return bool;

### 单文件模式
DNMVCS 还可利用 RouteHookOneFileMode
### 单目录模式
RouteHookDirectoryMode
## 控制器
### 自定义控制器
DNMVCS 的控制器没什么特殊的要求。
符合类名就行。
也不需要什么返回值。
### Miss 方法

### 助手类 ControllerHelper
DNMVCS 的助手类
## 数据库
## 模型
DNMVCS 的Model 实际相当于 DataStruct 的概念。
一表一个 Model，不要
### 助手类 Model

### 视图
### 
### 助手类 Model

## 服务/业务逻辑
### 助手类 Model

## 命令行

## 开发者： 单元测试

### 提供插件。

惯例， View 和 Configer 都要重新写