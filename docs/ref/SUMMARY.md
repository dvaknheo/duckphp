# Summary

1. **[DuckPhp](DuckPhp.md)** 入口类，加载了默认扩展的 DuckPhp 入口 ，扩展自 [DuckPhp\\Core\\App](Core-App.md)
2. `Core` 目录是核心目录，核心框架。基本功能都在 Core 里实现
    1. **[ComponentBase](Core-ComponentBase.md)** 组件基类
         1. **[ComponentInterface](Core-ComponentInterface.md)** 组件接口
    2. **[App](Core-App.md)** 核心应用类。引用以下类
        1. **[KernelTrait](Core-KernelTrait.md)** 核心Trait 以下是 `核心必备组件`
            1. [AutoLoader](Core-AutoLoader.md) 自动加载类
            2. **[Configer](Core-Configer.md)** 配置组件
            3. **[View](Core-View.md)** 视图组件
            4. **[Route](Core-Route.md)** 路由组件
            5. **[ExceptionManager](Core-ExceptionManager.md)**   异常管理组件
            6. **[RuntimeState](Core-RuntimeState.md)** 运行期数据保存组件
            7. **[Functions](Core-Functions.md)** 全局函数列表
        2. [ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md) 扩展静态调用的 trait
        3. [SystemWrapperTrait](Core-SystemWrapperTrait.md) 替换系统同名函数的 trait
        4. [Logger](Core-Logger.md) 日志组件
    3. **[Console](Component-Cache.md)** 命令行模式扩展组件
3. `Component` 目录，自带组件扩展。
    1. [AppPluginTrait](Component-AppPluginTrait.md)   这个Trait用于把独立工程 App 转成插件 
    2. [Cache](Component-Cache.md) 缓存组件
    5. [DuckPhpCommand](Component-DuckPhpCommand.md) DuckPhp 的默认指令组件
    6. [DbManager](Component-DbManager.md) 数据库管理组件
    7. [EventManager](Component-EventManager.md) 事件管理组件
    8. [Pager](Component-Pager.md) 分页类
        1. [PagerInteface](Component-PagerInteface.md) 分页接口
    9. **[RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)** 无程序路由设计模式组件
    10. **[RouteHookRouteMap](Component-RouteHookRouteMap.md)** 路由映射组件
4. `Db` 目录，数据库目录
    1. [DbAdvanceTrait](Db-DbAdvanceTrait.md)  这个 trait 增加了 Db类的高级功能
    2. [DbInterface](Db-DbInterface.md) Db 类满足 DbInterface 接口
    3. [Db](Db-Db.md) Db类
5. `Foundation` 目录。存放高级功能的目录
    2. [SimpleSessionTrait](Foundation-SimpleSessionTrait.md) 简单的会话Trait
    3. [SimpleControllerTrait](Foundation-SimpleControllerTrait.md) 简单的模型Trait
    4. [SimpleModelTrait](Foundation-SimpleModel.md) 简单的模型Trait
    6. [ThrowOnableTrait](Foundation-ThrowOnableTrait.md) 让类有ThrowOn功能
6. `Ext` 扩展目录，非默认加载的扩展。按字母排序。
    1. [CallableView](Ext-CallableView.md) 可接受函数调用的视图组件
    2. [EmptyView](Ext-EmptyView.md) 空视图组件
    3. [ExceptionWrapper](Ext-ExceptionWrapper.md) 异常包裹
    4. [HookChain](Ext-HookChain.md) 把回调扩展成链的类
    5. [HttpServerPlugin](Ext-HttpServerPlugin.md) TODO http 扩展插件
    6. [JsonRpcExt](Ext-JsonRpcExt.md) Json 远程调用组件，把本地调用改为远程调用
        1. [JsonRpcClientBase](Ext-JsonRpcClientBase.md)
    7. [JsonView](Ext-JsonView.md) Json 视图组件
    8. [Misc](Ext-Misc.md) 杂项功能组件
    9. [MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) 门面组件，不推荐
        1. [MyFacadesBase](Ext-MyFacadesBase.md) 门面类的基类，不推荐
    10. [MyMiddleware](Ext-MyMiddleware.md) 中间件，不推荐
    11. [RedisCache](Ext-RedisSimpleCache.md) redis 缓存组件
    12. [RedisManager](Ext-RedisManager.md) Redis管理器组件
    13. [RouteHookApiServer](Ext-RouteHookApiServer.md) 简单的 API 服务器插件
    14. [RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) 多个目录基准的模式组件
    15. [RouteHookManager](Ext-RouteHookManager.md) 路由钩子管理器
    16. [RouteHookRewrite](Ext-RouteHookRewrite.md) 路由重写组件
    17. [StaticReplacer](Ext-StaticReplacer.md) 适配协程的语法替换写法类
    18. [StrictCheck](Ext-StrictCheck.md) 严格检查模式组件
    19. [SuperGlobalContext](Ext-SuperGlobalContext.md) 超全局上下文组件
7. `Helper` 目录，各种助手类。
    + [AdvanceHelperTrait](Helper-AdvanceHelperTrait.md) 应用助手Trait
    + [BusinessHelperTrait](Helper-BusinessHelperTrait.md) 业务助手Trait
    + [ControllerHelper](Helper-ControllerHelper.md) 控制器助手类
    + [ControllerHelperTrait](Helper-ControllerHelperTrait.md) 控制器助手Trait
    + [ModelHelperTrait](Helper-ModelHelperTrait.md) 模型助手Trait
8. `HttpServer` 目录
    1. [AppInterface](HttpServer-AppInterface.md)  Http 服务的应用接口
    2. [HttpServer](HttpServer-HttpServer.md)  Http 服务器
    3. [HttpServerInterface](HttpServer-HttpServerInterface.md)  Http 服务接口
