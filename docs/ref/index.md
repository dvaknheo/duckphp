# 参考首页
[toc]
## 介绍

本参考是所有 DuckPhp 类 的规范参考。不包含内部 trait 。


[所有选项。](options.md)

## 架构图

![DuckPhp](../duckphp.gv.svg)


图解:

灰色尖框为可单独使用类

方尖为 Trait

组件类用统一标志


## 按 DuckPhp 文件结构介绍的类

遵循 PSR-4 的文件结构

链接指向参考文件。

1. `Core` 目录是核心目录，核心框架。基本功能都在 Core 里实现
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
    3. [AppPluginTrait](Core-AppPluginTrait.md)   这个Trait用于把独立工程 App 转成插件 
2. `Db` 目录，数据库目录
   1. [DbAdvanceTrait](Db-DbAdvanceTrait.md)  这个 trait 增加了 Db类的高级功能
   2. [DbInterface](Db-DbInterface.md) Db 类满足 DbInterface 接口
   3. [Db](Db-Db.md) Db类
3. **[DuckPhp](DuckPhp.md)** 入口类，加载了默认扩展的 DuckPhp 入口 ，扩展自 [DuckPhp\\Core\\App](Core-App.md)
4. `Component` 目录，自带组件扩展，**默认加载的扩展**。按字母排序。
   1. [Cache](Component-Cache.md) 缓存组件
   2. **[Console](Component-Cache.md)** 命令行模式扩展组件
   3. [Installer](Component-Installer.md) 安装器
   4. [DuckPhpCommand](Component-DuckPhpCommand.md) DuckPhp 的默认指令组件
   5. [DbManager](Component-DbManager.md) 数据库管理组件
   6. [EventManager](Component-EventManager.md) 事件管理组件
   7. [Pager](Component-Pager.md) 分页类
        1. [PagerInteface](Component-PagerInteface.md) 分页接口
   8. **[RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)** 无程序路由设计模式组件
   9. **[RouteHookRouteMap](Component-RouteHookRouteMap.md)** 路由映射组件

5. `Ext` 扩展目录，非默认加载的扩展。按字母排序。
    1. [CallableView](Ext-CallableView.md) 可接受函数调用的视图组件
    2. [EmptyView](Ext-EmptyView.md) 空视图组件
    3. [HookChain](Ext-HookChain.md) 把回调扩展成链的类
    4. [HttpServerPlugin](Ext-HttpServerPlugin.md) TODO http 扩展插件
    5. [JsonRpcExt](Ext-JsonRpcExt.md) Json 远程调用组件，把本地调用改为远程调用
        1. [JsonRpcClientBase](Ext-JsonRpcClientBase.md)
    6. [JsonView](Ext-JsonView.md) Json 视图组件
    7. [Misc](Ext-Misc.md) 杂项功能组件
    8. [MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) 门面组件，不推荐
        1. [MyFacadesBase](Ext-MyFacadesBase.md) 门面类的基类，不推荐
    9. [MyMiddleware](Ext-MyMiddleware.md) 中间件，不推荐
    10. [RedisCache](Ext-RedisSimpleCache.md) redis 缓存组件
    11. [RedisManager](Ext-RedisManager.md) Redis管理器组件
    12. [RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) 多个目录基准的模式组件
    13. [RouteHookManager](Ext-RouteHookManager.md) 路由钩子管理器
    14. [RouteHookRewrite](Ext-RouteHookRewrite.md) 路由重写组件
    15. [SimpleModel](Ext-SimpleModel.md) TODO 简单的模型基类
    16. [StaticReplacer](Ext-StaticReplacer.md) 适配协程的语法替换写法类
    17. [StrictCheck](Ext-StrictCheck.md) 严格检查模式组件
6. `Helper` 目录，各种助手类。
    1. [HelperTrait](Helper-HelperTrait.md) 助手类公用 Trait
    2. [ControllerHelper](Helper-ControllerHelper.md) 控制器助手类
    3. [ModelHelper](Helper-ModelHelper.md) 模型助手类
    4. [BusinessHelper](Helper-BusinessHelper.md) 服务助手类
    5. [ViewHelper](Helper-ViewHelper.md) 视图助手类
    6. *[AppHelper](Helper-AppHelper.md)* 工程应用助手类
7. `HttpServer` 目录
    1. [HttpServer](HttpServer-HttpServer.md)  Http 服务器
8. `SingletonEx`目录
    1. **[SingletonExTrait](SingletonEx-SingletonExTrait.md)**  可变单例 trait
    1. [SimpleReplacer](SingletonEx-SimpleReplacer.md)  可选可变单例容器
9. `ThrowOn`目录
    1. [ThrowOnTrait](ThrowOn-ThrowOnTrait.md) 可抛 trait，应用工程引用它方便异常处理

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

## nginx 配置


```
server {
    root DUCKPHP_ROOT/template/public;
    index index.php index.html index.htm;
    
    try_files $uri $uri/ /index.php$request_uri;
    location ~ \.php {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_split_path_info ^(.*\.php)(/.*)?$;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```
