# 参考首页

## 介绍

本参考是所有 DuckPHP 类 的规范参考。

## 按功能分类的类索引

### 助手
* [Helper\HelperTrait](Helper-HelperTrait.md) 助手类公用 Trait
* [Helper\ControllerHelper](Helper-ControllerHelper.md) 控制器助手类
* [Helper\ModelHelper](Helper-ModelHelper.md) 模型助手类
* [Helper\ServiceHelper](Helper-ServiceHelper.md) 服务助手类
* [Helper\ViewHelper](Helper-ViewHelper.md) 视图助手类
* [Helper\AppHelper](Helper-AppHelper.md) 服务助手类

### 入口
* [App](App.md) 框架应用入口类  继承
* [HttpServer](HttpServer.md) 自带 Http 服务器
* [SingletonEx](SingletonEx.md) 可变单例 Trait
* [ThrowOn](ThrowOn.md) 可抛 Trait

### 核心
* [Core\App](Core-App.md) 核心应用入口类
* [Core\AppPluginTrait](Core-AppPluginTrait.md)  插件 trait
* [Core\AutoLoader](Core-AutoLoader.md)  自动加载组件
* [Core\Configer](Core-Configer.md) 配置组件
* [Core\ExceptionManager](Core-ExceptionManager.md) 异常管理组件
* [Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md) 可扩展静态方法trait
* [Core\HttpServer](Core-HttpServer.md) Http 服务器
* [Core\Logger](Core-Logger.md) 日志组件
* [Core\Route](Core-Route.md) 路由组件
* [Core\RuntimeState](Core-RuntimeState.md) 运行状态保持的组件
* [Core\SingletonEx](Core-SingletonEx.md) 可变单例Trait
* [Core\SuperGlobal](Core-SuperGlobal.md) 超全局变量
* [Core\SystemWrapper](Core-SystemWrapper.md) 全局函数替代
* [Core\ThrowOn](Core-ThrowOn.md) 可抛Trait
* [Core\View](Core-View.md) 视图组件

### 数据库
* [DB\DB](DB-DB.md) 数据库类
* [DB\DBAdvance](DB-DBAdvance.md)  数据库扩展
* [DB\DBInterface](DB-DBInterface.md) 数据库类接口
### 扩展
* [Ext\CallableView](Ext-CallableView.md) 
* [Ext\DBManager](Ext-DBManager.md) 
* [Ext\DBReusePoolProxy](Ext-DBReusePoolProxy.md) 
* [Ext\FacadesAutoLoader](Ext-FacadesAutoLoader.md) 
* [Ext\FacadesBase](Ext-FacadesBase.md) 
* [Ext\HookChain](Ext-HookChain.md) 
* [Ext\JsonRpcClientBase](Ext-JsonRpcClientBase.md) 
* [Ext\JsonRpcExt](Ext-JsonRpcExt.md) 
* [Ext\Lazybones](Ext-Lazybones.md) 
* [Ext\Misc](Ext-Misc.md) 
* [Ext\Pager](Ext-Pager.md) 
* [Ext\PagerInterface](Ext-PagerInterface.md) 
* [Ext\PluginForSwooleHttpd](Ext-PluginForSwooleHttpd.md) 
* [Ext\RedisManager](Ext-RedisManager.md) 
* [Ext\RedisSimpleCache](Ext-RedisSimpleCache.md) 
* [Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) 
* [Ext\RouteHookOneFileMode](Ext-RouteHookOneFileMode.md) 
* [Ext\RouteHookRewrite](Ext-RouteHookRewrite.md) 
* [Ext\RouteHookRouteMap](Ext-RouteHookRouteMap.md) 
* [Ext\StrictCheck](Ext-StrictCheck.md) 
* [Ext\StrictCheckModelTrait](Ext-StrictCheckModelTrait.md) 
* [Ext\StrictCheckServiceTrait](Ext-StrictCheckServiceTrait.md) 
## DuckPHP 的源代码
遵循 PSR-4 的文件结构，节点文件是 `Core/Singleton` 是所有非辅助类都以来的可变单例类。
`Core/App`, `Core/Kernel`, `Core/HttpServer` 是连接性节点。 其他节点都是独立的。

链接指向参考文件。

1. [App.php](App.md) 是加载了扩展的 DuckPHP 入口 ，扩展至 Core/App

2. [HttpServer.php](HttpServer.md) 是 加了 Swoole 的 Http 服务器。

3. [SingletonEx.php](SingletonEx.md) 是 Core/SingletonEx.php 的快捷方法。

4. [ThrowOn.php](ThrowOn.md) 是 Core/ThrowOn.php 的快捷方法。

5. Core/ 目录是核心目录，基本功能都在 Core 里实现
   1. **[SingletonEx.php](Core-SingletonEx.php)**  
   2. **[ThrowOn.php](Core-ThrowOn.md)** 注意这个 trait 也被 Helper使用
   3. **[App.php](Core-App.md)** 是核心,引用
        1. [Kernel.php](Core-Kernel.md) 核心Trait 组件
           1. [AutoLoader.php](Core-AutoLoader.md)
           2. [Configer.php](Core-Configer.md)
           3. [View.php](Core-View.md)
           4. [Route.php](Core-Route.md)
           5. 以上是核心必备组件
           6. [SuperGlobal.php](SuperGlobal.md)
           7. [Logger.php](Core-Logger.md)
           8. [ExceptionManager.php](Core-ExceptionManager.md)  
           9. [RuntimeState.php](Core-RuntimeState.md)
        2. [ExtendableStaticCallTrait.php](Core-ExtendableStaticCallTrait.md) 注意这个 trait 也被 Helper使用
        3. [SystemWrapper.php](Core-SystemWrapper.md)
   3. **[AppPluginTrait.php](Core-AppPluginTrait.md) **  这个Trait用于把独立工程 App 转成插件 
   4. **[HttpServer.php](Core-HttpServer.md)** 单独的 Http 服务器
  
6. DB/ 是数据库
   1. [DBAdvance.php](DB-DBAdvance.md)
   2. [DBInterface.php](DB-DBInterface.md)
   3. [DB.php](DB-DB.md)

7. Ext/ 目录是各种扩展，粗体为默认
   1. **[DBManager.php](Ext-DBManager.md)**
   2. **[Misc.php](Ext-Misc.md)**
   3. **[Pager.php](Ext-Pager.md)**
        1. [PagerInteface.php](Ext-PagerInteface.md)
   4. **[RouteHookRewrite.php](Ext-RouteHookRewrite.md)**
   5. **[RouteHookRouteMap.php](Ext-RouteHookRouteMap.md)**
   6. [CallableView.php](Ext-CallableView.md)
   7. [DBReusePoolProxy.php](Ext-DBReusePoolProxy.md)
   8. [FacadesAutoLoader.php](Ext-FacadesAutoLoader.md)
        1. [FacadesBase.php](Ext-FacadesBase.md)
   9. [HookChain.php](Ext-HookChain.md) 这个独立文件没用到。
   10. [JsonRpcExt.php](Ext-JsonRpcExt.md)
        1. [JsonRpcClientBase.php](Ext-JsonRpcClientBase.md)
   11. [PluginForSwooleHttpd.php](Ext-PluginForSwooleHttpd.md)
   12. [RedisManager.php](Ext-RedisManager.md)
   13. [RedisSimpleCache.php](Ext-RedisSimpleCache.md)
   14. [RouteHookDirectoryMode.php](Ext-RouteHookDirectoryMode.md)
   15. [RouteHookOneFileMode.php](Ext-RouteHookOneFileMode.md)
   16. [StrictCheck.php](Ext-StrictCheck.md)
         1. [StrictCheckModelTrait.php](Ext-StrictCheckModelTrait.md)
         2. [StrictCheckServiceTrait.php](Ext-StrictCheckServiceTrait.md)
   17. *[Lazybones.php](Ext-Lazybones.md)*
8. Helper/ 助手类
    1. **[HelperTrait.php](Helper-HelperTrait.md)**
    2. [ControllerHelper.php](Helper-ControllerHelper.md)
    3. [ModelHelper.php](Helper-ModelHelper.md)
    4. [ServiceHelper.php](Helper-ServiceHelper.md)
    5. [ViewHelper.php](Helper-ViewHelper.md)
    6. *[AppHelper.php](Helper-AppHelper.md)*

## 按字母顺序的类索引

* [App](App.md) 
* [Core\App](Core-App.md) 
* [Core\AppPluginTrait](Core-AppPluginTrait.md) 
* [Core\AutoLoader](Core-AutoLoader.md) 
* [Core\Configer](Core-Configer.md) 
* [Core\ExceptionManager](Core-ExceptionManager.md) 
* [Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md) 
* [Core\HttpServer](Core-HttpServer.md) 
* [Core\Logger](Core-Logger.md) 
* [Core\Route](Core-Route.md) 
* [Core\RuntimeState](Core-RuntimeState.md) 
* [Core\SingletonEx](Core-SingletonEx.md) 
* [Core\SuperGlobal](Core-SuperGlobal.md) 
* [Core\SystemWrapper](Core-SystemWrapper.md) 
* [Core\ThrowOn](Core-ThrowOn.md) 
* [Core\View](Core-View.md) 
* [DB\DB](DB-DB.md) 
* [DB\DBAdvance](DB-DBAdvance.md) 
* [DB\DBInterface](DB-DBInterface.md) 
* [Ext\CallableView](Ext-CallableView.md) 
* [Ext\DBManager](Ext-DBManager.md) 
* [Ext\DBReusePoolProxy](Ext-DBReusePoolProxy.md) 
* [Ext\FacadesAutoLoader](Ext-FacadesAutoLoader.md) 
* [Ext\FacadesBase](Ext-FacadesBase.md) 
* [Ext\HookChain](Ext-HookChain.md) 
* [Ext\JsonRpcClientBase](Ext-JsonRpcClientBase.md) 
* [Ext\JsonRpcExt](Ext-JsonRpcExt.md) 
* [Ext\Lazybones](Ext-Lazybones.md) 
* [Ext\Misc](Ext-Misc.md) 
* [Ext\Pager](Ext-Pager.md) 
* [Ext\PagerInterface](Ext-PagerInterface.md) 
* [Ext\PluginForSwooleHttpd](Ext-PluginForSwooleHttpd.md) 
* [Ext\RedisManager](Ext-RedisManager.md) 
* [Ext\RedisSimpleCache](Ext-RedisSimpleCache.md) 
* [Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) 
* [Ext\RouteHookOneFileMode](Ext-RouteHookOneFileMode.md) 
* [Ext\RouteHookRewrite](Ext-RouteHookRewrite.md) 
* [Ext\RouteHookRouteMap](Ext-RouteHookRouteMap.md) 
* [Ext\StrictCheck](Ext-StrictCheck.md) 
* [Ext\StrictCheckModelTrait](Ext-StrictCheckModelTrait.md) 
* [Ext\StrictCheckServiceTrait](Ext-StrictCheckServiceTrait.md) 
* [Helper\ControllerHelper](Helper-ControllerHelper.md) 
* **[Helper\HelperTrait](Helper-HelperTrait.md)** 
* **[Helper\ModelHelper](Helper-ModelHelper.md)** 
* **[Helper\ServiceHelper](Helper-ServiceHelper.md)** 
* **[Helper\ViewHelper](Helper-ViewHelper.md)** 
* [HttpServer](HttpServer.md) 
* [SingletonEx](SingletonEx.md) 
* [ThrowOn](ThrowOn.md) 

## 选项列表

- 'all_config' => array (), //参见 [Core\Configer](Core-Configer.md)
    所有配置
    
- 'before_get_db_handler' => NULL,
    获取DB前调用的handler
    
- 'callable_view_head' => null,
    页眉
    
- 'callable_view_foot' => null,
    页脚
    
- 'callable_view_class' => null,

   视图类

- 'callable_view_prefix' => null,

   视图函数模板

- 'callable_view_skip_replace' => false,

    可调用视图跳过默认视图替换

- 'controller_base_class' => NULL,

    控制器基类

- 'controller_hide_boot_class' => false,

    控制器隐藏 特别的入口

- 'controller_methtod_for_miss' => '_missing',

    控制器，缺失方法
- 'controller_postfix' => '',

    控制器方法后缀
- 'controller_prefix_post' => 'do_',
    控制器，POST 方法前缀
- 'controller_welcome_class' => 'Main',
    控制器默认欢迎方法
- 'database_list' => NULL,
    数据库列表
- 'db_before_query_handler' => ['MY\\Base\\App','OnQuery']
    数据库，查询前执行
- 'db_close_at_output' => true,
    数据库，输出前关闭
- 'db_close_handler' => NULL,
    数据库，关闭句柄
- 'db_create_handler' => NULL,
    数据库，创建句柄
- 'db_exception_handler' => NULL,
    数据库，异常句柄
- 'default_exception_handler' => ['DuckPhp\\App',OnDefaultException']
    默认异常句柄

- 'dev_error_handler' => 'DuckPhp\\App','OnDevErrorHandler']
    默认开发错误句柄

- 'enable_cache_classes_in_cli' => false,
在 cli 下开启缓存模式
- 'error_404' => '_sys/error_404',
404 页面
- 'error_500' => '_sys/error_500',
500 页面
- 'error_debug' => '_sys/error_debug',
错误调试页面

- 'ext' => 
   array (
    'DuckPhp\\Ext\\Misc' => true,
    'DuckPhp\\Ext\\SimpleLogger' => true,
    'DuckPhp\\Ext\\DBManager' => true,
    'DuckPhp\\Ext\\RouteHookRewrite' => true,
    'DuckPhp\\Ext\\RouteHookRouteMap' => true,
    'DuckPhp\\Ext\\StrictCheck' => false,
    'DuckPhp\\Ext\\RouteHookOneFileMode' => false,
    'DuckPhp\\Ext\\RouteHookDirectoryMode' => false,
    'DuckPhp\\Ext\\RedisManager' => false,
    'DuckPhp\\Ext\\RedisSimpleCache' => false,
    'DuckPhp\\Ext\\DBReusePoolProxy' => false,
    'DuckPhp\\Ext\\FacadesAutoLoader' => false,
    'DuckPhp\\Ext\\Lazybones' => false,
    'DuckPhp\\Ext\\Pager' => false,
   ),

    默认开启扩展
- 'handle_all_dev_error' => true,
    接管一切开发错误
- 'handle_all_exception' => true,
    接管一切异常
- 'is_debug' => true,
    是否调试状态
- 'log_file' => '',
    日志文件
- 'log_prefix' => 'DuckPhpLog',
    日志前缀
- 'log_sql' => false,
    记录sql
- 'namespace' => 'MY',
    命名空间
- 'namespace_controller' => 'Controller',

    控制器的命名空间
- 'override_class' => 'Base\\App',
    重写类名
- 'path' => '/mnt/d/MyWork/sites/DNMVCS/template/',
    路径
- 'path_config' => 'config',
    配置路径
- 'path_lib' => 'lib',
    库路径
- 'path_namespace' => 'app',
    命名空间路径
- 'path_view' => 'view',
    视图路径
- 'path_view_override' => '',
    覆盖视图路径
- 'platform' => '',
    平台
- 'rewrite_map' => array ( ),
    路径重写映射

- 'route_map' => array ( ),
    路由映射

- 'route_map_important' => array ( ),
    重要路由映射

- 'setting' => array ( ),
    设置，预先载入的设置

- 'setting_file' => 'setting',

    设置文件

- 'skip_404_handler' => false,

    跳过404处理
- 'skip_app_autoload' => false,
    跳过 自动加载
- 'skip_env_file' => true,
    跳过 .env 文件
- 'skip_exception_check' => false,
    跳过异常检查
- 'skip_fix_path_info' => false,
    跳过 PATH_INFO 修复
- 'skip_plugin_mode_check' => false,
    跳过插件模式检查
- 'skip_setting_file' => true,
    跳过设置文件
- 'skip_system_autoload' => true,
    跳过 系统自动加载
- 'skip_view_notice_error' => true,
    跳过 View 视图的 notice
- 'system_exception_handler' =>  Duckphp->  set_exception_handler
    接管系统的异常管理
- 'use_context_db_setting' => true,
    使用父类的数据库配置
- 'use_flag_by_setting' => true,

    从设置文件里再入
- 'use_short_functions' => true,
使用短函数,使用

- 'use_super_global' => false,
    使用super_global 类。