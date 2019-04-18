# DNMVCS 教程
## 用 DNMVCS 的起因
最大原因懒，懒得自己写路由。
但是原生的 URL 不够美化,找个能用的路由。
最好是灵活方便修改的(注意这句话)
扩展方便。

直接在 github 上下载本项目，
```bash
php template/bin/start_server.php
```
浏览器中打开 http://127.0.0.1:8080/ 得到欢迎页 即可。


你也可以 composer 安装


```bash
composer require dnmvcs/framework
```
把网站目录指向 public/index.php 就行。

可我不是全站的。甚至，我都没法 path_info —— 没关系，你这个需求是有些诡异但可以解决。

我们接下来会逐步学习：

## 从入门到精通
1. 学习 DNCore 的配置
2. 调用 DNCore 类的静态方法实现目的
3. 调用 DNCore 类的动态方法实现目的
4. 学习 DNMVCS 的配置
4. 调用 DNMVCS 类的静态方法实现目的
5. 调用 DNMVCS 类的动态方法实现目的
6. 学习更高级的调用
7. ---- 核心程序员和高级程序员分界线 ----
8. 扩展 DNMVCS 类
9. 调用扩展类，组件类的动态方法实现目的
10. 继承接管，特定类实现目的
11. 魔改，硬改 DNMVCS 的代码实现目的

## 安装

### composer 安装

```bash
composer require dnmvcs/framework
php bin/start_server.php
```

浏览器中打开 http://127.0.0.1:8080/ 得到欢迎页


然后试着添加例子。



### 目录结构

默认的目录结构

```text
+---app                     // psr-4 标准的自动加载目录
|   +---Base                // 基类放在这里
|   |      App.php          // 默认框架入口文件
|   |      Contrllor.php    // 控制器基类
|   |      Model.php        // 模型基类
|   |      Service.php      // 服务基类
|   +---Controller          // 控制器目录
|   |       Main.php        // 默认控制器
|   +---Model               // 模型放在里
|   |       TestModel.php   // 测试模型
|   \---Service             // 服务目录
|           TestService.php // 测试 Service
+---bin                     // 命令行程序放这里
|       start_server.php    // 启动服务
+---config                  // 配置文件放这里
|       config.php          // 配置，目前是空数组
|       setting.sample.php  // 设置，去除敏感信息的模板
+---headfile                // 头文件处理
|       headfile.php       // 用于测试导入文件
+---view                    // 视图文件放这里，可调
|   |   main.php            // 视图文件
|   \---_sys                // 系统错误视图文件放这里
|           error-404.php   // 404
|           error-500.php   // 500 出错了
|           error-debug.php // 调试的时候显示的视图
|           error-exception.php // 出异常了，和 500 不同是 这里是未处理的异常。
\---public                  // 网站目录约定放这里
        index.php           // 主页
```
这些结构能精简么？
可以，你可以一个目录都不要。

## 第二步，跑 hello world

```php
<?php
require(__DIR__.'/../headfile/headfile.php');  //头文件

$path=realpath(__DIR__.'/..');
$options=[
    'path'=>$path,
    'namespace'=>'MY',
];
if (defined('DNMVCS_WARNING_IN_TEMPLATE')) {
    $options['setting_file_basename']='';
    $options['is_dev']=true;
    echo "<div>Don't run the template file directly </div>\n";
}
\DNMVCS\DNMVCS::RunQuickly($options);
// \DNMVCS\DNMVCS::G()->init($options)->run();
```

## 接下来是看这个 $options  默认配置有什么了：
```php
    const DEFAULT_OPTIONS=[
            'path'=>null,
            'namespace'=>'MY',
            'path_namespace'=>'app',
            'skip_app_autoload'=>false,
            
            //// properties ////
            'override_class'=>'Base\App',
            'is_dev'=>false,
            'platform'=>'',
            'path_view'=>'view',
            'path_config'=>'config',
            'skip_view_notice_error'=>true,
            'use_inner_error_view'=>false,
            
            //// config ////
            'setting_file_basename'=>'setting',
            'all_config'=>[],
            'setting'=>[],
            'reload_platform_and_dev'=>true,
            
            //// error handler ////
            'error_404'=>'_sys/error-404',
            'error_500'=>'_sys/error-500',
            'error_exception'=>'_sys/error-exception',
            'error_debug'=>'_sys/error-debug',
            
            //// controller ////
            'controller_base_class'=>null,
            'controller_prefix_post'=>'do_',
                'controller_enable_paramters'=>false,
                'controller_methtod_for_miss'=>null,
                'controller_hide_boot_class'=>false,
                'controller_welcome_class'=>'Main',
                'controller_index_method'=>'index',
        ];
```

这是基础的，后面还有一大堆的配置。
总之，这里很明白了。 path 根目录
    namespace 命名空间
    path_namespace ，autoload 的命名空间
    skip_app_autoload 设置为 true ，不管这命名空间
    'path_view'=>'view',  视图的目录，基于 path 配置  ,如果是 / 开头，是绝对目录
    'path_config'=>'config', 配置的目录，基于 path 配置,如果是 / 开头，是绝对目录

    'is_dev'=>false, 配置是否是在开发状态 * 设置文件的  is_dev 会覆盖
    'platform'=>'',  配置开发平台 * 设置文件的  platform 会覆盖

    skip_view_notice_error view 视图里忽略 notice 错误。
    enable_cache_classes_in_cli  缓存类，这项是为了性能搞的。

    'setting_file_basename'=>'setting', // 如果这项为空，那就不读设置文件了。

    'all_config'=>[],  合并入的 config; // 当你不想读取配置的时候从这里拿
    'setting'=>[],      合并入的 setting; // 当你不想读取配置的时候从这里拿设置


    'override_class'=>'Base\App',  这项后面再说



#### override_class
注意到 app/Base/App.php 这个文件 MY\Base\App extends DNMVCS\DNMVCS;


## 调参数。

OK 安装好了，用了 路由， URL 也要更改，所以我们要调用 DN::URL 来显示路由。

    URL 
    Show
    ShowBlock
    Setting
    Config
    LoadConfig
    H

    Platform
    Developing

    ExitJson
    ExitRedirect
    ExitRouteTo
    Exit404
    IsRunning
    Parameters

    ThrowOn

    header
    exit_system
//////////////////////
init
run
assignPathNamespace
addRouteHook
setViewWrapper
getRouteCallingMethod
setViewWrapper
assignViewData
assignExceptionHandler
setMultiExceptionHandler
setDefaultExceptionHandler

学会这些静态方法，恭喜，基本会用了。
----------------------
我还没学会数据库呢。

这部分是 DNMVCS 的内容

    const DEFAULT_OPTIONS_EX=[
            'use_db'=>true,
            'db_create_handler'=>'',
            'db_close_handler'=>'',
            'db_setting_key'=>'database_list',
            'database_list'=>[],
            
            'rewrite_map'=>[],
            'route_map'=>[],
            
            'ext'=>[],
            'swoole'=>[],
        ];
----
    RunWithoutPathInfo
    RunOneFileMode
    RunAsServer
    DB
    DB_W
    DB_R
    SG
    GLOBALS
    STATICS
    CLASS_STATICS
    setcookie
    set_exception_handler
    register_shutdown_function
    session_start
    session_destroy
    session_set_save_handler
    RecordsetUrl
    RecordsetH
----
    assignRewrite
    assignRoute

更多配置

## 第三步，使用其他配置

## 第四步，跳转

## 第五步，数据库
