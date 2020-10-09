# 参考首页
[toc]
## 介绍

本参考是所有 DuckPhp 类 的规范参考。不包含内部 trait 。

本参考也索引所有选项。

## 架构图

![DuckPhp](../duckphp.gv.svg)



图解:

灰色尖框为可单独使用类

方尖为 Trait

组件类用统一标志



## 按 DuckPhp 文件结构介绍的类

遵循 PSR-4 的文件结构，`Core/App`, `Core/Kernel`, `Core/HttpServer` 是连接性节点。 其他节点都是独立的。

链接指向参考文件。

1. `Core` 目录是核心目录，核心框架。基本功能都在 Core 里实现
    3. **[ComponentBase.php](Core-ComponentBase.md)** 组件基类
         4. [ComponentInterface.php](Core-ComponentInterface.md) 组件接口
    5. **[App.php](Core-App.md)** 核心应用类。
         1. [Kernel.php](Core-Kernel.md) 核心Trait 以下是 `核心必备组件`
            1. [AutoLoader.php](Core-AutoLoader.md) 自动加载类
            2. [Configer.php](Core-Configer.md) 配置组件
            3. [View.php](Core-View.md) 视图组件
            4. [Route.php](Core-Route.md) 路由组件
            5. [SuperGlobal.php](SuperGlobal.md) 超全局变量替换组件
            6. [Logger.php](Core-Logger.md) 日志组件
            7. [ExceptionManager.php](Core-ExceptionManager.md)   异常管理组件
            8. [RuntimeState.php](Core-RuntimeState.md) 运行期数据保存组件
         2. [ExtendableStaticCallTrait.php](Core-ExtendableStaticCallTrait.md) 扩展静态调用的 trait
         3. [SystemWrapperTrait.php](Core-SystemWrapperTrait.md) 替换系统同名函数的 trait
         4. [Functions.php](Core-Functions.md) 全局函数列表
    6. **[AppPluginTrait.php](Core-AppPluginTrait.md) **  这个Trait用于把独立工程 App 转成插件 
    7. [HttpServer.php](Core-HttpServer.md) 单独的 Http 服务器
2. `Db` 目录是数据库目录
   1. [DbAdvanceTrait.php](Db-DbAdvanceTrait.md)  这个 trait 增加了 Db类的高级功能
   2. [DbInterface.php](Db-DbInterface.md) Db 类满足 DbInterface 接口
   3. [Db.php](Db-Db.md) Db类
3. [DuckPhp.php](DuckPhp.md) 加载了默认扩展的 DuckPhp 入口 ，扩展自 Core/App
4. `Ext` 目录是扩展目录，按字母排序。粗体部分为默认加载的扩展
   1. **[Cache.php](Ext-Cache.md)** 空缓存类
   2. [CallableView.php](Ext-CallableView.md) 可接受函数调用的视图组件
   3. **[DbManager.php](Ext-DbManager.md)** 数据库管理组件
   4. [EmptyView.php](Ext-EmptyView.md) 空视图组件
   5. **[EventManager.php](Ext-EventManager.md)** 事件管理器
   6. [FacadesAutoLoader.php](Ext-FacadesAutoLoader.md) 门面组件用于偷懒
        1. [FacadesBase.php](Ext-FacadesBase.md) 门面类的基类
   7. [JsonRpcExt.php](Ext-JsonRpcExt.md) Json 远程调用组件
        1. [JsonRpcClientBase.php](Ext-JsonRpcClientBase.md)
   8. [Misc.php](Ext-Misc.md) 杂项功能组件
   9. **[Pager.php](Ext-Pager.md)** 分页类
        1. [PagerInteface.php](Ext-PagerInteface.md) 分页接口
   10. [RedisCache.php](Ext-RedisSimpleCache.md) redis 缓存组件
   11. [RedisManager.php](Ext-RedisManager.md) Redis管理器组件
   12. [RouteHookDirectoryMode.php](Ext-RouteHookDirectoryMode.md) 多个目录基准的模式组件
   13. [RouteHookManager.php](Ext-RouteHookManager.md) 路由钩子管理器
   14. **[RouteHookPathInfoCompat.php](Ext-RouteHookPathInfoCompat.md) **无程序路由设计模式组件
   15. **[RouteHookRouteMap.php](Ext-RouteHookRouteMap.md)** 路由映射组件
   16. [RouteHookRewrite.php](Ext-RouteHookRewrite.md) 路由重写组件
   17. [StrictCheck.php](Ext-StrictCheck.md) 严格检查模式组件
5. `Helper` 目录，各种助手类
    1. **[HelperTrait.php](Helper-HelperTrait.md)** 助手类公用 Trait
    2. [ControllerHelper.php](Helper-ControllerHelper.md) 控制器助手类
    3. [ModelHelper.php](Helper-ModelHelper.md) 模型助手类
    4. [BusinessHelper.php](Helper-BusinessHelper.md) 服务助手类
    5. [ViewHelper.php](Helper-ViewHelper.md) 视图助手类
    6. *[AppHelper.php](Helper-AppHelper.md)* 工程应用助手类
6. `HttpServer` 目录
    1. [HttpServer.php](HttpServer-HttpServer.md)  Http 服务器。
7. `SingletonEx`目录
    1. **[SingletonEx.php](SingletonEx-SingletonEx.php)**  可变单例trait
8. `ThrowOn`目录
    1. **[ThrowOn.php](ThrowOn-ThrowOn.md)** 可抛 trait

##  全部文件一览

```
export LC_ALL='C';tree src

src
|-- Core
|   |-- App.php
|   |-- AppPluginTrait.php
|   |-- AutoLoader.php
|   |-- ComponentBase.php
|   |-- ComponentInterface.php
|   |-- Configer.php
|   |-- ExceptionManager.php
|   |-- ExtendableStaticCallTrait.php
|   |-- Functions.php
|   |-- Kernel.php
|   |-- Logger.php
|   |-- Route.php
|   |-- RuntimeState.php
|   |-- SuperGlobal.php
|   |-- SystemWrapperTrait.php
|   `-- View.php
|-- Db
|   |-- Db.php
|   |-- DbAdvanceTrait.php
|   `-- DbInterface.php
|-- DuckPhp.php
|-- Ext
|   |-- Cache.php
|   |-- CallableView.php
|   |-- DbManager.php
|   |-- EmptyView.php
|   |-- EventManager.php
|   |-- FacadesAutoLoader.php
|   |-- FacadesBase.php
|   |-- JsonRpcClientBase.php
|   |-- JsonRpcExt.php
|   |-- Misc.php
|   |-- Pager.php
|   |-- PagerInterface.php
|   |-- RedisCache.php
|   |-- RedisManager.php
|   |-- RouteHookApiServer.php
|   |-- RouteHookDirectoryMode.php
|   |-- RouteHookManager.php
|   |-- RouteHookPathInfoCompat.php
|   |-- RouteHookRewrite.php
|   |-- RouteHookRouteMap.php
|   `-- StrictCheck.php
|-- Helper
|   |-- AppHelper.php
|   |-- BusinessHelper.php
|   |-- ControllerHelper.php
|   |-- HelperTrait.php
|   |-- ModelHelper.php
|   `-- ViewHelper.php
|-- HttpServer
|   `-- HttpServer.php
|-- SingletonEx
|   `-- SingletonEx.php
`-- ThrowOn
    `-- ThrowOn.php
```
## 选项索引
按字母顺序，加粗表示默认选项。

@forscript genoptions.php#options-md-alpha
+ ** 'all_config' => array ( ),  ** 

    所有配置   // [DuckPhp\Core\Configer](Core-Configer.md)
+  'api_class_base' => 'BaseApi',   

    api 服务接口   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+  'api_class_prefix' => 'Api_',   

    api类的前缀   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+  'api_config_file' => '',   

    api配置文件   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+ ** 'autoload_cache_in_cli' => false,  ** 

    在 cli 下开启缓存模式   // [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+ ** 'autoload_path_namespace_map' => array ( ),  ** 

    自动加载的目录和命名空间映射   // [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+  'callable_view_class' => NULL,   

    callableview 视图类   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+  'callable_view_foot' => NULL,   

    callableview 页脚   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+  'callable_view_head' => NULL,   

    callableview 页眉   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+  'callable_view_prefix' => NULL,   

    callableview 视图函数模板   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+  'callable_view_skip_replace' => false,   

    callableview 可调用视图跳过默认视图替换   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ ** 'close_resource_at_output' => false,  ** 

    在输出前关闭资源（DB,Redis）   // [DuckPhp\Core\App](Core-App.md)
+ ** 'config_ext_files' => array ( ),  ** 

    额外的配置文件数组   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'controller_base_class' => NULL,  ** 

    控制器基类   // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ ** 'controller_class_postfix' => '',  ** 

    控制器类名后缀   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_enable_slash' => false,  ** 

    激活兼容后缀的 /    // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_hide_boot_class' => false,  ** 

    控制器标记，隐藏特别的入口   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_methtod_for_miss' => '_missing',  ** 

    控制器，缺失方法的调用方法   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_path_ext' => '',  ** 

    扩展名，比如你要 .html   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_prefix_post' => 'do_',  ** 

    控制器，POST 方法前缀   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_welcome_class' => 'Main',  ** 

    控制器默认欢迎方法   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'database' => NULL,  ** 

    单一数据库配置   // [DuckPhp\Ext\DbManager](Ext-DbManager.md)
+ ** 'database_list' => NULL,  ** 

    数据库列表   // [DuckPhp\Ext\DbManager](Ext-DbManager.md)
+ ** 'database_list_reload_by_setting' => true,  ** 

    从设置里读取数据库列表   // [DuckPhp\Ext\DbManager](Ext-DbManager.md)
+ ** 'database_list_try_single' => true,  ** 

    尝试使用单一数据库配置   // [DuckPhp\Ext\DbManager](Ext-DbManager.md)
+ ** 'database_log_sql_level' => 'debug',  ** 

    记录sql 错误等级   // [DuckPhp\Ext\DbManager](Ext-DbManager.md)
+ ** 'database_log_sql_query' => false,  ** 

    记录sql 查询   // [DuckPhp\Ext\DbManager](Ext-DbManager.md)
+ ** 'default_exception_do_log' => true,  ** 

    错误的时候打开日志   // [DuckPhp\Core\App](Core-App.md)
+  'default_exception_handler' => NULL,   

    默认异常句柄   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ ** 'default_exception_self_display' => true,  ** 

    错误的时候打开日志   // [DuckPhp\Core\App](Core-App.md)
+  'dev_error_handler' => NULL,   

    默认开发错误句柄   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+  'empty_view_key_view' => 'view',   

    给View 的key   // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+  'empty_view_key_wellcome_class' => 'Main/',   

    默认的 Main   // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+  'empty_view_skip_replace' => false,   

    跳过默认的view   // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+  'empty_view_trim_view_wellcome' => true,   

    跳过 Main/   // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ ** 'error_404' => NULL,  ** 

    404 页面   // [DuckPhp\Core\App](Core-App.md)
+ ** 'error_500' => NULL,  ** 

    500 页面   // [DuckPhp\Core\App](Core-App.md)
+ ** 'error_debug' => NULL,  ** 

    错误调试页面   // [DuckPhp\Core\App](Core-App.md)
+ ** 'ext' => array ( ),  ** 

    默认开启的扩展   // [DuckPhp\Core\App](Core-App.md)
+  'facades_enable_autoload' => true,   

    使用 facdes 的 autoload   // [DuckPhp\Ext\FacadesAutoLoader](Ext-FacadesAutoLoader.md)
+  'facades_map' => array ( ),   

    facade 映射   // [DuckPhp\Ext\FacadesAutoLoader](Ext-FacadesAutoLoader.md)
+  'facades_namespace' => 'Facades',   

    facades 开始的namespace   // [DuckPhp\Ext\FacadesAutoLoader](Ext-FacadesAutoLoader.md)
+ ** 'handle_all_dev_error' => true,  ** 

    接管一切开发错误   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ ** 'handle_all_exception' => true,  ** 

    接管一切异常   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ ** 'is_debug' => false,  ** 

    是否调试状态   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'jsonrpc_backend' => 'https://127.0.0.1',   

    json 的后端   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_check_token_handler' => NULL,   

    设置 token 检查回调   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_enable_autoload' => true,   

    json 启用 autoload   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_is_debug' => false,   

    jsonrpc 是否开启 debug 模式   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_namespace' => 'JsonRpc',   

    jsonrpc 默认的命名空间   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_service_interface' => '',   

    json 服务接口   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_service_namespace' => '',   

    json 命名空间   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_wrap_auto_adjust' => true,   

    jsonrpc 自动调整 wrap   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+ ** 'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',  ** 

    日志文件名模板   // [DuckPhp\Core\Logger](Core-Logger.md)
+ ** 'log_prefix' => 'DuckPhpLog',  ** 

    日志前缀   // [DuckPhp\Core\Logger](Core-Logger.md)
+  'mode_dir_basepath' => '',   

    目录模式的基类   // [DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md)
+ ** 'namespace' => 'LazyToChange',  ** 

    命名空间   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\AutoLoader](Core-AutoLoader.md), [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'namespace_business' => '',   

    strict_check 的business目录   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ ** 'namespace_controller' => 'Controller',  ** 

    控制器的命名空间   // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'namespace_model' => '',   

    strict_check 的model 目录   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ ** 'override_class' => 'System\\App',  ** 

    重写类名   // [DuckPhp\Core\App](Core-App.md)
+ ** 'path' => '',  ** 

    基础目录   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\AutoLoader](Core-AutoLoader.md), [DuckPhp\Core\Configer](Core-Configer.md), [DuckPhp\Core\Logger](Core-Logger.md), [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md), [DuckPhp\Ext\Misc](Ext-Misc.md)
+ ** 'path_config' => 'config',  ** 

    配置目录   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'path_info_compact_action_key' => '_r',  ** 

    GET 动作方法名的 key   // [DuckPhp\Ext\RouteHookPathInfoCompat](Ext-RouteHookPathInfoCompat.md)
+ ** 'path_info_compact_class_key' => '',  ** 

    GET 模式类名的 key   // [DuckPhp\Ext\RouteHookPathInfoCompat](Ext-RouteHookPathInfoCompat.md)
+ ** 'path_info_compact_enable' => false,  ** 

    使用 _GET 模拟无 PathInfo 配置   // [DuckPhp\Ext\RouteHookPathInfoCompat](Ext-RouteHookPathInfoCompat.md)
+  'path_lib' => 'lib',   

    库目录   // [DuckPhp\Ext\Misc](Ext-Misc.md)
+ ** 'path_log' => 'logs',  ** 

    日志目录   // [DuckPhp\Core\Logger](Core-Logger.md)
+ ** 'path_namespace' => 'app',  ** 

    命名空间目录   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+ ** 'path_view' => 'view',  ** 

    视图目录   // [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ ** 'path_view_override' => '',  ** 

    覆盖视图目录   // [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ ** 'platform' => '',  ** 

    平台   // [DuckPhp\Core\App](Core-App.md)
+  'postfix_batch_business' => 'BatchBusiness',   

    batchbusiness   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'postfix_business_lib' => 'Lib',   

     businesslib   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'postfix_ex_model' => 'ExModel',   

    ExModel   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'postfix_model' => 'Model',   

    model   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'redis' => NULL,   

    单一Redisc配置   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+  'redis_cache_prefix' => '',   

     redis cache 缓存前缀   // [DuckPhp\Ext\RedisCache](Ext-RedisCache.md)
+  'redis_cache_skip_replace' => false,   

    redis cache 跳过 默认 cache替换   // [DuckPhp\Ext\RedisCache](Ext-RedisCache.md)
+  'redis_list' => NULL,   

     redis 配置列表   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+  'redis_list_reload_by_setting' => true,   

     redis 使用 settting 文件   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+  'redis_list_try_single' => true,   

    尝试使用单一Redis配置   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+  'rewrite_map' => array ( ),   

    目录重写映射   // [DuckPhp\Ext\RouteHookRewrite](Ext-RouteHookRewrite.md)
+ ** 'route_map' => array ( ),  ** 

    路由映射   // [DuckPhp\Ext\RouteHookRouteMap](Ext-RouteHookRouteMap.md)
+ ** 'route_map_by_config_name' => '',  ** 

    路由配置名，使用配置模式用路由   // [DuckPhp\Ext\RouteHookRouteMap](Ext-RouteHookRouteMap.md)
+ ** 'route_map_important' => array ( ),  ** 

    重要路由映射   // [DuckPhp\Ext\RouteHookRouteMap](Ext-RouteHookRouteMap.md)
+ ** 'setting' => array ( ),  ** 

    设置，预先载入的设置   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'setting_file' => 'setting',  ** 

    设置文件   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'skip_404_handler' => false,  ** 

    跳过404处理   // [DuckPhp\Core\App](Core-App.md)
+ ** 'skip_app_autoload' => false,  ** 

    跳过 自动加载   // [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+ ** 'skip_env_file' => true,  ** 

    跳过 .env 文件   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'skip_exception_check' => false,  ** 

    跳过异常检查   // [DuckPhp\Core\App](Core-App.md)
+ ** 'skip_fix_path_info' => false,  ** 

    跳过 PATH_INFO 修复   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'skip_plugin_mode_check' => false,  ** 

    跳过插件模式检查   // [DuckPhp\Core\App](Core-App.md)
+ ** 'skip_setting_file' => false,  ** 

    跳过设置文件   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'skip_view_notice_error' => true,  ** 

    跳过 View 视图的 notice   // [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+  'strict_check_context_class' => NULL,   

    不用传输过来的 app类，而是特别指定类   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'strict_check_enable' => true,   

    是否开启 strict chck   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'system_exception_handler' => NULL,   

    接管系统的异常管理   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ ** 'use_autoloader' => true,  ** 

    使用系统自带加载器   // [DuckPhp\Core\App](Core-App.md)
+ ** 'use_flag_by_setting' => true,  ** 

    从设置文件里再入is_debug,platform.    // [DuckPhp\Core\App](Core-App.md)
+ ** 'use_output_buffer' => false,  ** 

    使用 OB 函数缓冲数据   // [DuckPhp\Core\RuntimeState](Core-RuntimeState.md)
+ ** 'use_short_functions' => true,  ** 

    使用短函数， \_\_url, \_\_h 等 ，详见 Core\Functions.php   // [DuckPhp\Core\App](Core-App.md)
+ ** 'use_super_global' => true,  ** 

    使用super_global 类。关闭以节约性能   // [DuckPhp\Core\App](Core-App.md)

@forscript end

## 选项索引
按类名排序加粗表示默认选项。

@forscript genoptions.php#options-md-class
+ DuckPhp\Core\App
    - 'close_resource_at_output' => false,
        在输出前关闭资源（DB,Redis）
    - 'default_exception_do_log' => true,
        错误的时候打开日志
    - 'default_exception_self_display' => true,
        错误的时候打开日志
    - 'error_404' => NULL,
        404 页面
    - 'error_500' => NULL,
        500 页面
    - 'error_debug' => NULL,
        错误调试页面
    - 'ext' => array ( ),
        默认开启的扩展
    - 'handle_all_dev_error' => true,
        接管一切开发错误
    - 'handle_all_exception' => true,
        接管一切异常
    - 'is_debug' => false,
        是否调试状态
    - 'namespace' => 'LazyToChange',
        命名空间
    - 'override_class' => 'System\\App',
        重写类名
    - 'path' => '',
        基础目录
    - 'path_namespace' => 'app',
        命名空间目录
    - 'platform' => '',
        平台
    - 'skip_404_handler' => false,
        跳过404处理
    - 'skip_exception_check' => false,
        跳过异常检查
    - 'skip_plugin_mode_check' => false,
        跳过插件模式检查
    - 'use_autoloader' => true,
        使用系统自带加载器
    - 'use_flag_by_setting' => true,
        从设置文件里再入is_debug,platform. 
    - 'use_short_functions' => true,
        使用短函数， \_\_url, \_\_h 等 ，详见 Core\Functions.php
    - 'use_super_global' => true,
        使用super_global 类。关闭以节约性能
+ DuckPhp\Core\AutoLoader
    - 'autoload_cache_in_cli' => false,
        在 cli 下开启缓存模式
    - 'autoload_path_namespace_map' => array ( ),
        自动加载的目录和命名空间映射
    - 'namespace' => 'LazyToChange',
        命名空间
    - 'path' => '',
        基础目录
    - 'path_namespace' => 'app',
        命名空间目录
    - 'skip_app_autoload' => false,
        跳过 自动加载
+ DuckPhp\Core\Configer
    - 'all_config' => array ( ),
        所有配置
    - 'config_ext_files' => array ( ),
        额外的配置文件数组
    - 'path' => '',
        基础目录
    - 'path_config' => 'config',
        配置目录
    - 'setting' => array ( ),
        设置，预先载入的设置
    - 'setting_file' => 'setting',
        设置文件
    - 'skip_env_file' => true,
        跳过 .env 文件
    - 'skip_setting_file' => false,
        跳过设置文件
+ DuckPhp\Core\ExceptionManager
    - 'default_exception_handler' => NULL,
        默认异常句柄
    - 'dev_error_handler' => NULL,
        默认开发错误句柄
    - 'handle_all_dev_error' => true,
        接管一切开发错误
    - 'handle_all_exception' => true,
        接管一切异常
    - 'system_exception_handler' => NULL,
        接管系统的异常管理
+ DuckPhp\Core\Logger
    - 'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',
        日志文件名模板
    - 'log_prefix' => 'DuckPhpLog',
        日志前缀
    - 'path' => '',
        基础目录
    - 'path_log' => 'logs',
        日志目录
+ DuckPhp\Core\Route
    - 'controller_base_class' => NULL,
        控制器基类
    - 'controller_class_postfix' => '',
        控制器类名后缀
    - 'controller_enable_slash' => false,
        激活兼容后缀的 / 
    - 'controller_hide_boot_class' => false,
        控制器标记，隐藏特别的入口
    - 'controller_methtod_for_miss' => '_missing',
        控制器，缺失方法的调用方法
    - 'controller_path_ext' => '',
        扩展名，比如你要 .html
    - 'controller_prefix_post' => 'do_',
        控制器，POST 方法前缀
    - 'controller_welcome_class' => 'Main',
        控制器默认欢迎方法
    - 'namespace' => 'LazyToChange',
        命名空间
    - 'namespace_controller' => 'Controller',
        控制器的命名空间
    - 'skip_fix_path_info' => false,
        跳过 PATH_INFO 修复
+ DuckPhp\Core\RuntimeState
    - 'use_output_buffer' => false,
        使用 OB 函数缓冲数据
+ DuckPhp\Core\SuperGlobal
+ DuckPhp\Core\View
    - 'path' => '',
        基础目录
    - 'path_view' => 'view',
        视图目录
    - 'path_view_override' => '',
        覆盖视图目录
    - 'skip_view_notice_error' => true,
        跳过 View 视图的 notice
+ DuckPhp\Ext\CallableView
    - 'callable_view_class' => NULL,
        callableview 视图类
    - 'callable_view_foot' => NULL,
        callableview 页脚
    - 'callable_view_head' => NULL,
        callableview 页眉
    - 'callable_view_prefix' => NULL,
        callableview 视图函数模板
    - 'callable_view_skip_replace' => false,
        callableview 可调用视图跳过默认视图替换
    - 'path' => '',
        基础目录
    - 'path_view' => 'view',
        视图目录
    - 'path_view_override' => '',
        覆盖视图目录
    - 'skip_view_notice_error' => true,
        跳过 View 视图的 notice
+ DuckPhp\Ext\Cache
+ DuckPhp\Ext\DbManager
    - 'database' => NULL,
        单一数据库配置
    - 'database_list' => NULL,
        数据库列表
    - 'database_list_reload_by_setting' => true,
        从设置里读取数据库列表
    - 'database_list_try_single' => true,
        尝试使用单一数据库配置
    - 'database_log_sql_level' => 'debug',
        记录sql 错误等级
    - 'database_log_sql_query' => false,
        记录sql 查询
+ DuckPhp\Ext\EmptyView
    - 'empty_view_key_view' => 'view',
        给View 的key
    - 'empty_view_key_wellcome_class' => 'Main/',
        默认的 Main
    - 'empty_view_skip_replace' => false,
        跳过默认的view
    - 'empty_view_trim_view_wellcome' => true,
        跳过 Main/
    - 'path' => '',
        基础目录
    - 'path_view' => 'view',
        视图目录
    - 'path_view_override' => '',
        覆盖视图目录
    - 'skip_view_notice_error' => true,
        跳过 View 视图的 notice
+ DuckPhp\Ext\EventManager
+ DuckPhp\Ext\FacadesAutoLoader
    - 'facades_enable_autoload' => true,
        使用 facdes 的 autoload
    - 'facades_map' => array ( ),
        facade 映射
    - 'facades_namespace' => 'Facades',
        facades 开始的namespace
+ DuckPhp\Ext\JsonRpcExt
    - 'jsonrpc_backend' => 'https://127.0.0.1',
        json 的后端
    - 'jsonrpc_check_token_handler' => NULL,
        设置 token 检查回调
    - 'jsonrpc_enable_autoload' => true,
        json 启用 autoload
    - 'jsonrpc_is_debug' => false,
        jsonrpc 是否开启 debug 模式
    - 'jsonrpc_namespace' => 'JsonRpc',
        jsonrpc 默认的命名空间
    - 'jsonrpc_service_interface' => '',
        json 服务接口
    - 'jsonrpc_service_namespace' => '',
        json 命名空间
    - 'jsonrpc_wrap_auto_adjust' => true,
        jsonrpc 自动调整 wrap
+ DuckPhp\Ext\Misc
    - 'path' => '',
        基础目录
    - 'path_lib' => 'lib',
        库目录
+ DuckPhp\Ext\RedisCache
    - 'redis_cache_prefix' => '',
         redis cache 缓存前缀
    - 'redis_cache_skip_replace' => false,
        redis cache 跳过 默认 cache替换
+ DuckPhp\Ext\RedisManager
    - 'redis' => NULL,
        单一Redisc配置
    - 'redis_list' => NULL,
         redis 配置列表
    - 'redis_list_reload_by_setting' => true,
         redis 使用 settting 文件
    - 'redis_list_try_single' => true,
        尝试使用单一Redis配置
+ DuckPhp\Ext\RouteHookApiServer
    - 'api_class_base' => 'BaseApi',
        api 服务接口
    - 'api_class_prefix' => 'Api_',
        api类的前缀
    - 'api_config_file' => '',
        api配置文件
+ DuckPhp\Ext\RouteHookDirectoryMode
    - 'mode_dir_basepath' => '',
        目录模式的基类
+ DuckPhp\Ext\RouteHookPathInfoCompat
    - 'path_info_compact_action_key' => '_r',
        GET 动作方法名的 key
    - 'path_info_compact_class_key' => '',
        GET 模式类名的 key
    - 'path_info_compact_enable' => false,
        使用 _GET 模拟无 PathInfo 配置
+ DuckPhp\Ext\RouteHookRewrite
    - 'rewrite_map' => array ( ),
        目录重写映射
+ DuckPhp\Ext\RouteHookRouteMap
    - 'route_map' => array ( ),
        路由映射
    - 'route_map_by_config_name' => '',
        路由配置名，使用配置模式用路由
    - 'route_map_important' => array ( ),
        重要路由映射
+ DuckPhp\Ext\StrictCheck
    - 'controller_base_class' => NULL,
        控制器基类
    - 'is_debug' => false,
        是否调试状态
    - 'namespace' => 'LazyToChange',
        命名空间
    - 'namespace_business' => '',
        strict_check 的business目录
    - 'namespace_controller' => 'Controller',
        控制器的命名空间
    - 'namespace_model' => '',
        strict_check 的model 目录
    - 'postfix_batch_business' => 'BatchBusiness',
        batchbusiness
    - 'postfix_business_lib' => 'Lib',
         businesslib
    - 'postfix_ex_model' => 'ExModel',
        ExModel
    - 'postfix_model' => 'Model',
        model
    - 'strict_check_context_class' => NULL,
        不用传输过来的 app类，而是特别指定类
    - 'strict_check_enable' => true,
        是否开启 strict chck

@forscript end

## 其他选项
这几个选项，不是放在 $options 的，所以特地在这里参考
### DuckPhp\Core\AppPluginTrait

    'plugin_path_namespace' => null,
    'plugin_namespace' => null,
    
    'plugin_routehook_position' => 'append-outter',
    
    'plugin_path_conifg' => 'config',
    'plugin_path_view' => 'view',
    
    'plugin_search_config' => false,
    'plugin_use_helper' => true,
    'plugin_files_config' => [],
    'plugin_url_prefix' => '',
###  DuckPhp\HttpServer\HttpServer

    'host' => '127.0.0.1',
    'port' => '8080',
    'path' => '',
    'path_document' => 'public',
### DuckPhp\Ext\Pager

    'url' => null,
    'current' => null,
    'page_size' => 30,
    'page_key' => 'page',
    'rewrite' => null,
    'pager_context_class' => null,