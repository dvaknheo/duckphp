# 整体架构图
## DuckPHP 的源代码
遵循 PSR-4 的文件结构，节点文件是 `Core/Singleton` 是所有非辅助类都以来的可变单例类。
`Core/App`, `Core/Kernel`, `Core/HttpServer` 是连接性节点。 其他节点都是独立的。

链接指向参考文件。

1. [App.php](ref/App.md) 是加载了扩展的 DuckPHP 入口 ，扩展至 Core/App

2. [HttpServer.php](ref/HttpServer.md) 是 加了 Swoole 的 Http 服务器。

3. [SingletonEx.php](ref/SingletonEx.md) 是 Core/SingletonEx.php 的快捷方法。

4. Core/ 目录是核心目录，基本功能都在 Core 里实现
   - **[SingletonEx.php](ref/Core-SingletonEx.php)**  
   - **[App.php](ref/Core-App.md)** 是核心,引用
     - [Kernel.php](ref/Core-Kernel.md) 核心Trait 组件
        1. [AutoLoader.php](ref/Core-AutoLoader.md)
        2. [Configer.php](ref/Core-Configer.md)
        3. [View.php](ref/Core-View.md)
        4. [Route.php](ref/Core-Route.md)
        5. [SuperGlobal.php](ref/SuperGlobal.md)
        6. [Logger.php](ref/Core-Logger.md)
        7. [ExceptionManager.php](ref/Core-ExceptionManager.md)  
        8. [RuntimeState.php](ref/Core-RuntimeState.md)
     - [ThrowOn.php](ref/Core-ThrowOn.md) 注意这个 trait 也被 Helper使用
     - [ExtendableStaticCallTrait.php](ref/Core-ExtendableStaticCallTrait.md) 注意这个 trait 也被 Helper使用
     - [SystemWrapper.php](ref/Core-SystemWrapper.md)
   - **[AppPluginTrait.php](ref/Core-AppPluginTrait.md) **  这个Trait用于把独立工程App转成插件 
   - **[HttpServer.php](ref/Core-HttpServer.md)**

5. DB/ 是数据库
   - [DBAdvance.php](ref/DB-DBAdvance.md)
   - [DBInterface.php](ref/DB-DBInterface.md)
   - [DB.php](ref/DB-DB.md)

6. Ext/ 目录是各种扩展，粗体为默认
   - [CallableView.php](ref/Ext-CallableView.md)
   - **[DBManager.php](ref/Ext-DBManager.md)**
   - [DBReusePoolProxy.php](ref/Ext-DBReusePoolProxy.md)
   - [FacadesAutoLoader.php](ref/Ext-FacadesAutoLoader.md)
     - [FacadesBase.php](ref/Ext-FacadesBase.md)
   - [HookChain.php](ref/Ext-HookChain.md) 这个独立文件没用到。
   - [JsonRpcExt.php](ref/Ext-JsonRpcExt.md)
     - [JsonRpcClientBase.php](ref/Ext-JsonRpcClientBase.md)
   - *[Lazybones.php](ref/Ext-Lazybones.md)*
   - **[Misc.php](ref/Ext-Misc.md)**
   - **[Pager.php](ref/Ext-Pager.md)**
   - [PluginForSwooleHttpd.php](ref/Ext-PluginForSwooleHttpd.md)
   - [RedisManager.php](ref/Ext-RedisManager.md)
   - [RedisSimpleCache.php](ref/Ext-RedisSimpleCache.md)
   - [RouteHookDirectoryMode.php](ref/Ext-RouteHookDirectoryMode.md)
   - [RouteHookOneFileMode.php](ref/Ext-RouteHookOneFileMode.md)
   - **[RouteHookRewrite.php](ref/Ext-RouteHookRewrite.md)**
   - **[RouteHookRouteMap.php](ref/Ext-RouteHookRouteMap.md)**
   - [StrictCheck.php](ref/Ext-StrictCheck.md)
       - [StrictCheckModelTrait.php](ref/Ext-StrictCheckModelTrait.md)
       - [StrictCheckServiceTrait.php](ref/Ext-StrictCheckServiceTrait.md)

7. Helper/ 助手类

    1. **[HelperTrait.php](ref/Helper-HelperTrait.md)**

    - [AppHelper.php](ref/Helper-AppHelper.md)
    - [ControllerHelper.php](ref/Helper-ControllerHelper.md)
    - [ModelHelper.php](ref/Helper-ModelHelper.md)
    - [ServiceHelper.php](ref/Helper-ServiceHelper.md)
    - [ViewHelper.php](ref/Helper-ViewHelper.md)

## DuckPHP 全框架架构图
画成引用  [ SVG,下载查看大图 ](duckphp.gv.svg) （源文件 duckphp.gv）  如下：

钻石表示核心节点

Ext 的虚线，表示的是默认未加载的扩展。

![DuckPHP](duckphp.gv.svg)