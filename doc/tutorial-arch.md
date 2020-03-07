# 整体架构图
## DuckPHP 的源代码
遵循 PSR-4 的文件结构，节点文件是 `Core/Singleton` 是所有非辅助类都以来的可变单例类。
`Core/App`, `Core/Kernel`, `Core/HttpServer` 是连接性节点。 其他节点都是独立的。

链接指向参考文件。

1. [App.php]() 是加载了扩展的 DuckPHP 入口 ，扩展至 Core/App

2. [HttpServer.php]() 是 加了 Swoole 的 Http 服务器。

3. [SingletonEx.php]() 是 Core/SingletonEx.php 的快捷方法。

4. Core/ 目录是核心目录，基本功能都在 Core 里实现
   - **[SingletonEx.php]()**  

   - **[App.php]()** 是核心,引用

     - [Kernel.php]() 核心Trait 组件

        1. [AutoLoader.php]()

        2. [Configer.php]()

        3. [View.php]()

        4. [Route.php]()

        5. [SuperGlobal.php]()

           

        6. [Logger.php]()

        7. [ExceptionManager.php]()  

        8. [RuntimeState.php]()

     - [ThrowOn.php]() 注意这个 trait 也被 Helper使用

     - [ExtendableStaticCallTrait.php]() 注意这个 trait 也被 Helper使用

     - [SystemWrapper.php]()

   - **[AppPluginTrait.php]() ** 这个Trait 

   - **[HttpServer.php]()**

5. DB/ 是数据库
   - [DBAdvance.php]()
   - [DBInterface.php]()
   - [DB.php]()

6. Ext/ 目录是各种扩展，粗体为默认
   - [CallableView.php]()
   - **[DBManager.php]()**
   - [DBReusePoolProxy.php]()
   - [FacadesAutoLoader.php]()
     - [FacadesBase.php]()
   - [HookChain.php]() 这个独立文件没用到。
   - [JsonRpcExt.php]()
     - [JsonRpcClientBase.php]()
   - *[Lazybones.php]()*
   - **[Misc.php]()**
   - **[Pager.php]()**
   - [PluginForSwooleHttpd.php]()
   - [RedisManager.php]()
   - [RedisSimpleCache.php]()
   - [RouteHookDirectoryMode.php]()
   - [RouteHookOneFileMode.php]()
   - **[RouteHookRewrite.php]()**
   - **[RouteHookRouteMap.php]()**
   - [StrictCheck.php]()
       - [StrictCheckModelTrait.php]()
       - [StrictCheckServiceTrait.php]()

7. Helper/ 助手类

    1. **[HelperTrait.php]()**

    - [AppHelper.php]()
    - [ControllerHelper.php]()
    - [ModelHelper.php]()
    - [ServiceHelper.php]()
    - [ViewHelper.php]()

## DuckPHP 全框架架构图
画成引用[ SVG,下载查看大图 ](duckphp.gv.svg) 如下：

钻石表示核心节点

Ext 的虚线，表示的是默认未加载的扩展。

![DuckPHP](duckphp.gv.svg)

