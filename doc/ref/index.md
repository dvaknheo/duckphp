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
* [Helper\HelperTrait](Helper-HelperTrait.md) 
* [Helper\ModelHelper](Helper-ModelHelper.md) 
* [Helper\ServiceHelper](Helper-ServiceHelper.md) 
* [Helper\ViewHelper](Helper-ViewHelper.md) 
* [HttpServer](HttpServer.md) 
* [SingletonEx](SingletonEx.md) 
* [ThrowOn](ThrowOn.md) 

## 选项列表
