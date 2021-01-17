# Summary

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