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

@script filedesc

1. **[DuckPhp](DuckPhp.md)** 入口类，加载了默认扩展的 DuckPhp 入口 ，扩展自 [DuckPhp\\Core\\App](Core-App.md)
2. **[DuckPhpAllInOne](DuckPhpAllInOne.md)** 入口类，绑定所有助手Trait的入口类;
3. [AutoLoad\AutoLoader](Core-AutoLoader.md) 自动加载类
2. `Core` 目录是核心目录，核心框架。基本功能都在 Core 里实现
    1. **[ComponentBase](Core-ComponentBase.md)** 组件基类
        1. **[ComponentInterface](Core-ComponentInterface.md)** 组件接口
        2. **[SingletonTrait](Core-SingletonTrait.md)** 单例模式 
    2. **[App](Core-App.md)** 核心应用类。引用以下类
        -  **[KernelTrait](Core-KernelTrait.md)** 核心Trait 以下是 `核心必备组件`
        -  **[Console](Core-Console.md)** 命令行模式扩展组件
        -  **[CoreHelp](Core-CoreHelp.md)** 核心助手类
        -  **[DuckPhpSystemException](Core-DuckPhpSystemException.md)** 核心助手类
        -  [EventManager](Core-EventManager.md) 事件管理组件
        -  **[ExceptionManager](Core-ExceptionManager.md)**   异常管理组件
        -  **[ExitException](Core-ExitException.md)** 退出异常
        -  **[Functions](Core-Functions.md)** 全局函数列表
        -  [Logger](Core-Logger.md) 日志组件
        -  [PhaseContainer](Core-PhaseContainer.md) 容器类，相位容器类
        -  **[Route](Core-Route.md)** 路由组件
        -  **[Runtime](Core-Runtime.md)** 运行期数据保存组件
        -  [SuperGlobal](Ext-SuperGlobal.md) 超全局上下文组件
        -  [SystemWrapper](Core-SystemWrapper.md) 替换系统同名函数的 trait
        -  **[ThrownOnTrait](Core-ThrownOnTrait.md)** 可抛方法
        -  **[View](Core-View.md)** 视图组件
3. `Component` 目录，自带组件扩展。
    -  [Cache](Component-Cache.md) 缓存组件
    -  **[Configer](Core-Configer.md)** 配置组件
    -  [DbManager](Component-DbManager.md) 数据库管理组件
    -  [DuckPhpCommand](Component-DuckPhpCommand.md) DuckPhp 的默认指令组件
    -  [DuckPhpInstaller](Component-DuckPhpInstaller.md) DuckPhp 的安装组件
    -  [ExtOptionsLoader](Component-ExtOptionsLoader.md) 额外选项组件
    -  [GlobalAdmin](Component-GlobalAdmin.md) 全局管理员组件
    -  [GlobalUser](Component-GlobalUser.md) 全局用户组件
    -  [Pager](Component-Pager.md) 分页类
        1. [PagerInteface](Component-PagerInteface.md) 分页接口
    -  [PhaseProxy](Component-PhaseProxy.md) 分页类
    -  [RedisCache](Component-RedisSimpleCache.md) redis 缓存组件
    -  [RedisManager](Component-RedisManager.md) Redis管理器组件
    -  **[RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)** 无程序路由设计模式组件
    -  **[RouteHookResource](Component-RouteHookResource.md)** 资源组件
    -  **[RouteHookRouteMap](Component-RouteHookRouteMap.md)** 路由映射组件
    -  [RouteHookRewrite](Component-RouteHookRewrite.md) 路由重写组件
    -  [SqlDumper](Component-SqlDumper.md) 数据库加载组件
4. `Db` 目录，数据库目录
    1. [DbAdvanceTrait](Db-DbAdvanceTrait.md)  这个 trait 增加了 Db类的高级功能
    2. [DbInterface](Db-DbInterface.md) Db 类满足 DbInterface 接口
    3. [Db](Db-Db.md) Db类
5. `Foundation` 目录。存放高级功能的目录
    - [ExceptionReporterTrait](Foundation-ExceptionReporterTrait.md) 错误报告Trait
    - [SimpleBusinessTrait](Foundation-SimpleBusinessTrait.md) 简单的模型Trait
    - [SimpleControllerTrait](Foundation-SimpleControllerTrait.md) 简单的模型Trait
    - [SimpleExceptionTrait](Foundation-SimpleExceptionTrait.md) 让类有ThrowOn功能
    - [SimpleModelTrait](Foundation-SimpleModel.md) 简单的模型Trait
    - [SimpleSessionTrait](Foundation-SimpleSessionTrait.md) 简单的会话Trait
    - [SimpleSingletonTrait](Foundation-SimpleSingletonTrait.md) 单例Trait
    - [Business/Helper](Foundation-Business-Helper.md) 提供业务助手类
    - [Controller/Helper](Foundation-Helper.md) 控制器助手类
    - [Model/Helper](Foundation-Helper.md) 模型助手类
    - [System/Helper](Foundation-Helper.md) 系统助手类
    - [Helper](Foundation-Helper.md) 集合所有的助手类
    - [FastInstallerTrait](Foundation-FastInstallerTrait.md) 快速安装器助手类
    
6. `Ext` 扩展目录，非默认加载的扩展。按字母排序。
    -  [CallableView](Ext-CallableView.md) 可接受函数调用的视图组件
    +  [EmptyView](Ext-EmptyView.md) 空视图组件
    +  [ExceptionWrapper](Ext-ExceptionWrapper.md) 异常包裹
    +  [ExtendableStaticCallTrait](Ext-ExtendableStaticCallTrait.md) 扩展静态调用的 trait
    +  [HookChain](Ext-HookChain.md) 把回调扩展成链的类
    +  [JsonRpcExt](Ext-JsonRpcExt.md) Json 远程调用组件，把本地调用改为远程调用
        1. [JsonRpcClientBase](Ext-JsonRpcClientBase.md)
    +  [JsonView](Ext-JsonView.md) Json 视图组件
    +  [MiniRoute](Ext-MiniRoute.md) 简化版的路由组件
    +  [Misc](Ext-Misc.md) 杂项功能组件
    +  [MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) 门面组件，不推荐
        1. [MyFacadesBase](Ext-MyFacadesBase.md) 门面类的基类，不推荐
    +  [MyMiddlewareManager](Ext-MyMiddlewareManager.md) 中间件，不推荐
    +  [RouteHookApiServer](Ext-RouteHookApiServer.md) 简单的 API 服务器插件
    +  [RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) 多个目录基准的模式组件
    +  [RouteHookManager](Ext-RouteHookManager.md) 路由钩子管理器
    +  [RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) 支持函数调用
    +  [StaticReplacer](Ext-StaticReplacer.md) 适配协程的语法替换写法类
    +  [StrictCheck](Ext-StrictCheck.md) 严格检查模式组件
7. `Helper` 目录，各种助手类。
    + [AdvanceHelperTrait](Helper-AdvanceHelperTrait.md) 应用助手Trait
    + [BusinessHelperTrait](Helper-BusinessHelperTrait.md) 业务助手Trait
    + [ControllerHelperTrait](Helper-ControllerHelperTrait.md) 控制器助手Trait
    + [ModelHelperTrait](Helper-ModelHelperTrait.md) 模型助手Trait
8. `HttpServer` 目录
    2. [HttpServer](HttpServer-HttpServer.md)  Http 服务器
    3. [HttpServerInterface](HttpServer-HttpServerInterface.md)  Http 服务接口
6. `FastInstaller` 目录。 一个安装程序.
    + [FastInstaller](FastInstaller-FastInstaller.md) 安装程序入口
        - [DatabaseInstaller](Ext-DatabaseInstaller.md) 数据库安装器
        - [RedisInstaller](Ext-RedisInstaller.md) Redis数据库安装器
        - [SqlDumper](Ext-SqlDumper.md) 数据库结构导出器


@script filedesc

在本参考中，所有的类的方法都已经用脚本检查，不存在有类的方法无文档的情况

所有的选项也用脚本检查，不存在有类的选项没遗漏的情况

##  全部文件一览

```
tree src
src
├── Component
│   ├── Cache.php
│   ├── Configer.php
│   ├── DbManager.php
│   ├── DuckPhpCommand.php
│   ├── DuckPhpInstaller.php
│   ├── ExtOptionsLoader.php
│   ├── GlobalAdmin.php
│   ├── GlobalUser.php
│   ├── Pager.php
│   ├── PagerInterface.php
│   ├── PhaseProxy.php
│   ├── RedisCache.php
│   ├── RedisManager.php
│   ├── RouteHookPathInfoCompat.php
│   ├── RouteHookResource.php
│   ├── RouteHookRewrite.php
│   ├── RouteHookRouteMap.php
│   └── SqlDumper.php
├── Core
│   ├── App.php
│   ├── AutoLoader.php
│   ├── ComponentBase.php
│   ├── ComponentInterface.php
│   ├── Console.php
│   ├── CoreHelper.php
│   ├── DuckPhpSystemException.php
│   ├── EventManager.php
│   ├── ExceptionManager.php
│   ├── ExitException.php
│   ├── Functions.php
│   ├── KernelTrait.php
│   ├── Logger.php
│   ├── PhaseContainer.php
│   ├── Route.php
│   ├── Runtime.php
│   ├── SingletonTrait.php
│   ├── SuperGlobal.php
│   ├── SystemWrapper.php
│   ├── ThrowOnTrait.php
│   └── View.php
├── Db
│   ├── Db.php
│   ├── DbAdvanceTrait.php
│   └── DbInterface.php
├── DuckPhp.php
├── DuckPhpAllInOne.php
├── Ext
│   ├── CallableView.php
│   ├── EmptyView.php
│   ├── ExceptionWrapper.php
│   ├── ExtendableStaticCallTrait.php
│   ├── HookChain.php
│   ├── JsonRpcClientBase.php
│   ├── JsonRpcExt.php
│   ├── JsonView.php
│   ├── MiniRoute.php
│   ├── Misc.php
│   ├── MyFacadesAutoLoader.php
│   ├── MyFacadesBase.php
│   ├── MyMiddlewareManager.php
│   ├── RouteHookApiServer.php
│   ├── RouteHookDirectoryMode.php
│   ├── RouteHookFunctionRoute.php
│   ├── RouteHookManager.php
│   ├── StaticReplacer.php
│   └── StrictCheck.php
├── Foundation
│   ├── ExceptionReporterTrait.php
│   ├── SimpleBusinessTrait.php
│   ├── SimpleControllerTrait.php
│   ├── SimpleExceptionTrait.php
│   ├── SimpleModelTrait.php
│   ├── SimpleSessionTrait.php
│   └── SimpleSingletonTrait.php
├── Helper
│   ├── AppHelperTrait.php
│   ├── BusinessHelperTrait.php
│   ├── ControllerHelperTrait.php
│   └── ModelHelperTrait.php
└── HttpServer
    ├── AppInterface.php
    ├── HttpServer.php
    └── HttpServerInterface.php

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
