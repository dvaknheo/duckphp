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

1. [App.php](App.md) 加载了默认扩展的 DuckPhp 入口 ，扩展自 Core/App
2. [HttpServer.php](HttpServer.md)  加了 Swoole 的 Http 服务器。
3. `Core` 目录是核心目录，核心框架。基本功能都在 Core 里实现
   1. **[SingletonEx.php](Core-SingletonEx.php)**  可变单例trait
   2. **[ThrowOn.php](Core-ThrowOn.md)** 可抛 trait
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
4. `DB` 目录是数据库目录
   1. [DBAdvanceTrait.php](DB-DBAdvanceTrait.md)  这个 trait 增加了 DB类的高级功能
   2. [DBInterface.php](DB-DBInterface.md) DB 类满足 DBInterface 接口
   3. [DB.php](DB-DB.md) DB类
5. `Ext` 目录是扩展目录，按字母排序。默认加载  **[DBManager.php](Ext-DBManager.md)**   **[RouteHookRouteMap.php](Ext-RouteHookRouteMap.md)**
   1. **[DBManager.php](Ext-DBManager.md)** 数据库管理组件
   3. **[Pager.php](Ext-Pager.md)** 分页类
        1. [PagerInteface.php](Ext-PagerInteface.md) 分页接口
   4. **[RouteHookRouteMap.php](Ext-RouteHookRouteMap.md)** 路由映射组件
   4. [RouteHookRewrite.php](Ext-RouteHookRewrite.md) 路由重写组件
   5. [Misc.php](Ext-Misc.md) 杂项功能组件
   6. [CallableView.php](Ext-CallableView.md) 可接受函数调用的视图组件
   7. [DBReusePoolProxy.php](Ext-DBReusePoolProxy.md) DB连接池组件，小心使用
   8. [EmptyView.php](Ext-EmptyView.md) 空视图组件
   9. [FacadesAutoLoader.php](Ext-FacadesAutoLoader.md) 门面组件用于偷懒
        1. [FacadesBase.php](Ext-FacadesBase.md) 门面类的基类
   10. [JsonRpcExt.php](Ext-JsonRpcExt.md) Json 远程调用组件
         1. [JsonRpcClientBase.php](Ext-JsonRpcClientBase.md)
   11. [PluginForSwooleHttpd.php](Ext-PluginForSwooleHttpd.md) 支持 SwooleHttpd 的组件
   12. [RedisManager.php](Ext-RedisManager.md) Redis管理器组件
   13. [RedisSimpleCache.php](Ext-RedisSimpleCache.md) redis 缓存组件
   14. [RouteHookDirectoryMode.php](Ext-RouteHookDirectoryMode.md) 多个目录基准的模式组件
   15. [RouteHookOneFileMode.php](Ext-RouteHookOneFileMode.md) 无程序路由设计模式组件
   16. [StrictCheck.php](Ext-StrictCheck.md) 严格检查模式组件
            1. [StrictCheckModelTrait.php](Ext-StrictCheckModelTrait.md) 严格检查模式的模型类基类
                 2. [StrictCheckServiceTrait.php](Ext-StrictCheckServiceTrait.md) 严格检查模式的服务类基类
6. `Helper` 目录是各种助手类
    1. **[HelperTrait.php](Helper-HelperTrait.md)** 助手类公用 Trait
    2. [ControllerHelper.php](Helper-ControllerHelper.md) 控制器助手类
    3. [ModelHelper.php](Helper-ModelHelper.md) 模型助手类
    4. [ServiceHelper.php](Helper-ServiceHelper.md) 服务助手类
    5. [ViewHelper.php](Helper-ViewHelper.md) 视图助手类
    6. *[AppHelper.php](Helper-AppHelper.md)* 工程应用助手类
    
##  全部文件一览

```
.
|-- App.php
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
|   |-- HttpServer.php
|   |-- Kernel.php
|   |-- Logger.php
|   |-- Route.php
|   |-- RuntimeState.php
|   |-- SingletonEx.php
|   |-- SuperGlobal.php
|   |-- SystemWrapperTrait.php
|   |-- ThrowOn.php
|   `-- View.php
|-- DB
|   |-- DB.php
|   |-- DBAdvanceTrait.php
|   `-- DBInterface.php
|-- Ext
|   |-- CallableView.php
|   |-- DBManager.php
|   |-- DBReusePoolProxy.php
|   |-- EmptyView.php
|   |-- FacadesAutoLoader.php
|   |-- FacadesBase.php
|   |-- JsonRpcClientBase.php
|   |-- JsonRpcExt.php
|   |-- Misc.php
|   |-- Pager.php
|   |-- PagerInterface.php
|   |-- PluginForSwooleHttpd.php
|   |-- RedisManager.php
|   |-- RedisSimpleCache.php
|   |-- RouteHookDirectoryMode.php
|   |-- RouteHookManager.php.todo
|   |-- RouteHookOneFileMode.php
|   |-- RouteHookRewrite.php
|   |-- RouteHookRouteMap.php
|   |-- StrictCheck.php
|   |-- StrictCheckModelTrait.php
|   `-- StrictCheckServiceTrait.php
|-- Helper
|   |-- AppHelper.php
|   |-- ControllerHelper.php
|   |-- HelperTrait.php
|   |-- ModelHelper.php
|   |-- ServiceHelper.php
|   `-- ViewHelper.php
`-- HttpServer.php
```
##
## 选项索引
- 'all_config' => array (), // 参见 [Core\Configer](Core-Configer.md)

    所有配置    
- 'before\_get\_db\_handler' => NULL, // 参见 [Ext\DBManager](Ext-DBManager.md)

    获取DB前调用的handler
- 'callable_view_head' => null, // 参见 [Ext\CallableView](Ext-CallableView.md)

    callableview 页眉
- 'callable_view_foot' => null, // 参见 [Ext\CallableView](Ext-CallableView.md)

    callableview 页脚    
- 'callable_view_class' => null, // 参见 [Ext\CallableView](Ext-CallableView.md)

   callableview 视图类
- 'callable_view_prefix' => null, // 参见 [Ext\CallableView](Ext-CallableView.md)

   callableview 视图函数模板
- 'callable_view_skip_replace' => false, // 参见 [Ext\CallableView](Ext-CallableView.md)

    callableview 可调用视图跳过默认视图替换
- 'controller_base_class' => NULL, // 参见 [Core\Route](Core-Route.md)

    控制器基类
- 'controller_hide_boot_class' => false, // 参见 [Core\Route](Core-Route.md)

    控制器标记，隐藏特别的入口
- 'controller_methtod_for_miss' => '_missing', // 参见 [Core\Route](Core-Route.md)

    控制器，缺失方法的调用方法
- 'controller_postfix' => '', // 参见 [Core\Route](Core-Route.md)

    控制器方法后缀
- 'controller_prefix_post' => 'do_', // 参见 [Core\Route](Core-Route.md)

    控制器，POST 方法前缀
- 'controller_welcome_class' => 'Main', // 参见 [Core\Route](Core-Route.md)

    控制器默认欢迎方法
- 'database_list' => NULL, // 参见 [Ext\DBManager](Ext-DBManager.md)

    数据库列表
- 'db_before_query_handler' => ['MY\\Base\\App','OnQuery'] // 参见 [Ext\XX](Ext-XX.md)

    数据库，查询前执行
- 'db_close_at_output' => true, // 参见 [Ext\DBManager](Ext-DBManager.md)

    数据库，输出前关闭
- 'db_close_handler' => NULL, // 参见 [Ext\DBManager](Ext-DBManager.md)

    数据库，关闭句柄
- 'db_create_handler' => NULL, // 参见 [Ext\DBManager](Ext-DBManager.md)

    数据库，创建句柄
- 'db_exception_handler' => NULL, // 参见 [Ext\DBManager](Ext-DBManager.md)

    数据库，异常句柄
- 'default_exception_handler' => ['DuckPhp\\App',OnDefaultException'] // 参见 [Ext\DBManager](Ext-DBManager.md)
    默认异常句柄

- 'dev_error_handler' => 'DuckPhp\\App','OnDevErrorHandler'] // 参见 [Core\Kernel](Core-Kernel.md)

    默认开发错误句柄
- 'enable_cache_classes_in_cli' => false, // 参见 [Core\AutoLoader](Core\AutoLoader.md)

    在 cli 下开启缓存模式
- 'error_404' => '\_sys/error_404',  // 参见 [Core\Kernel](Core-Kernel.md)

    404 页面
- 'error_500' => '\_sys/error_500',   // 参见 [Core\Kernel](Core-Kernel.md)

    500 页面
- 'error_debug' => '\_sys/error_debug',  // 参见 [Core\Kernel](Core-Kernel.md)

    错误调试页面
- 'ext' =>  // 参见 [Core\Kernel](Core-Kernel.md)

   array (
    'DuckPhp\\Ext\\Misc' => true,
    'DuckPhp\\Ext\\SimpleLogger' => true,
    'DuckPhp\\Ext\\DBManager' => true,
    'DuckPhp\\Ext\\RouteHookRewrite' => true,
    'DuckPhp\\Ext\\RouteHookRouteMap' => true,
   
   ),
   
    默认开启的扩展
   
- 'handle_all_dev_error' => true, // 参见 [Core\Kernel](Core-Kernel.md)

    接管一切开发错误
- 'handle_all_exception' => true, // 参见 [Core\Kernel](Core-Kernel.md)

    接管一切异常
- 'is_debug' => true, // 参见 [Core\Kernel](Core-Kernel.md)

    是否调试状态
- 'log_file' => '', // 参见 [Core\Logger](Core-Logger.md)

    日志文件
- 'log_prefix' => 'DuckPhpLog', // 参见 [Core\Logger](Core-Logger.md)

    日志前缀
- 'log_sql' => false,  // 参见 [App](App.md)

    记录sql
- 'namespace' => 'MY', // 参见 [Core\Kernel](Core-Kernel.md)

    命名空间
- 'namespace_controller' => 'Controller', // 参见 [Core\Route](Core-Route.md)

    控制器的命名空间
- 'override_class' => 'Base\\App', // 参见 [Core\Kernel](Core-Kernel.md)

    重写类名
- 'path' => '@ProjectPath', // 参见 [Core\Kernel](Core-Kernel.md)

    路径
- 'path_config' => 'config', // 参见 [Core\Configer](Core-Configer.md)

    配置路径
- 'path_lib' => 'lib',  // 参见 [Ext\Misc](Ext-Misc.md)

    库路径
- 'path_namespace' => 'app', // 参见 [Core\Autoloader](Core\AutoLoader.md)

    命名空间路径
- 'path_view' => 'view', // 参见 [Core\View](Core-View.md)

    视图路径
- 'path_view_override' => '', // 参见 [Core\View](Core-View.md)

    覆盖视图路径
- 'platform' => '', // 参见 [Core\Kernel](Core-Kernel.md)

    平台
- 'rewrite_map' => array ( ), // 参见 [Ext\RouteHookRewrite](Ext-RouteHookRewrite.md)

    路径重写映射
- 'route_map' => array ( ), // 参见 [Ext\RouteHookRouteMap](Ext-RouteHookRouteMap.md)

    路由映射
- 'route_map_important' => array ( ), // 参见 [Ext\RouteHookRouteMap](Ext-RouteHookRouteMap.md)

    重要路由映射
- 'setting' => array ( ), // 参见 [Core\Configer](Core-Configer.md)

    设置，预先载入的设置
- 'setting_file' => 'setting', // 参见 [Core\Configer](Core-Configer.md)

    设置文件
- 'skip_404_handler' => false, // 参见 [Core\Kernel](Core-Kernel.md)

    跳过404处理
- 'skip_app_autoload' => false, // 参见 [Core\Autoloader](Core\AutoLoader.md)

    跳过 自动加载
- 'skip_env_file' => true, // 参见 [Core\Configer](Core-Configer.md)

    跳过 .env 文件
- 'skip_exception_check' => false, // 参见 [Core\Kernel](Core-Kernel.md)

    跳过异常检查
- 'skip_fix_path_info' => false, // 参见 [Core\Kernel](Core-Kernel.md)

    跳过 PATH_INFO 修复
- 'skip_plugin_mode_check' => false, // 参见 [Core\Kernel](Core-Kernel.md)

    跳过插件模式检查
- 'skip_setting_file' => true, // 参见 [Core\Configer](Core-Configer.md)

    跳过设置文件
- 'skip_system_autoload' => true, // 参见 [Core\AutoLoader](Core-AutoLoader.md)

    跳过 系统自动加载
- 'skip_view_notice_error' => true, // 参见 [Core\Kernel](Core-Kernel.md)

    跳过 View 视图的 notice
- 'system_exception_handler' =>  Duckphp\App->set_exception_handler // 参见 [Core\Kernel](Core-Kernel.md)

    接管系统的异常管理
- 'use_context_db_setting' => true, // 参见 [Ext\DBManager](Ext-DBManager.md)

    使用父类的数据库配置
- 'use_flag_by_setting' => true, // 参见 [Core\Kernel](Core-Kernel.md)

    从设置文件里再入
- 'use_short_functions' => true, // 参见 [App](App.md)

    使用短函数
- 'use_super_global' => false, // 参见 [Core\Kernel](Core-Kernel.md)
    使用super_global 类。 