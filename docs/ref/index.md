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
    1. **[ComponentBase.php](Core-ComponentBase.md)** 组件基类
         1. [ComponentInterface.php](Core-ComponentInterface.md) 组件接口
    2. **[App.php](Core-App.md)** 核心应用类。
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
    3. **[AppPluginTrait.php](Core-AppPluginTrait.md) **  这个Trait用于把独立工程 App 转成插件 
2. `Db` 目录是数据库目录
   1. [DbAdvanceTrait.php](Db-DbAdvanceTrait.md)  这个 trait 增加了 Db类的高级功能
   2. [DbInterface.php](Db-DbInterface.md) Db 类满足 DbInterface 接口
   3. [Db.php](Db-Db.md) Db类
3. [DuckPhp.php](DuckPhp.md) 加载了默认扩展的 DuckPhp 入口 ，扩展自 Core/App
4. `Ext` 目录是扩展目录，按字母排序。默认加载的扩展
   1. **[Cache.php](Ext-Cache.md)** 空缓存类
   2. **[Console.php](Ext-Cache.md)** 空缓存类
        1. [Installer.php](Ext-Installer.md) 安装器
   3. **[DbManager.php](Ext-DbManager.md)** 数据库管理组件
   4. **[EventManager.php](Ext-EventManager.md)** 事件管理器
   5. **[Pager.php](Ext-Pager.md)** 分页类
        1. [PagerInteface.php](Ext-PagerInteface.md) 分页接口
   6. **[RouteHookPathInfoCompat.php](Ext-RouteHookPathInfoCompat.md)** 无程序路由设计模式组件
   7. **[RouteHookRouteMap.php](Ext-RouteHookRouteMap.md)** 路由映射组件

5. `Ext` 目录是扩展目录，按字母排序。非默认加载的扩展
   1. [CallableView.php](Ext-CallableView.md) 可接受函数调用的视图组件
   2. [EmptyView.php](Ext-EmptyView.md) 空视图组件
   3. [FacadesAutoLoader.php](Ext-FacadesAutoLoader.md) 门面组件用于偷懒
        1. [FacadesBase.php](Ext-FacadesBase.md) 门面类的基类
   4. [JsonRpcExt.php](Ext-JsonRpcExt.md) Json 远程调用组件
        1. [JsonRpcClientBase.php](Ext-JsonRpcClientBase.md)
   5. [JsonView.php](Ext-JsonView.md) Json 视图组件
   6. [Misc.php](Ext-Misc.md) 杂项功能组件
   7. [RedisCache.php](Ext-RedisSimpleCache.md) redis 缓存组件
   8. [RedisManager.php](Ext-RedisManager.md) Redis管理器组件
   9. [RouteHookDirectoryMode.php](Ext-RouteHookDirectoryMode.md) 多个目录基准的模式组件
   10. [RouteHookManager.php](Ext-RouteHookManager.md) 路由钩子管理器
   11. [RouteHookRewrite.php](Ext-RouteHookRewrite.md) 路由重写组件
   12. [StrictCheck.php](Ext-StrictCheck.md) 严格检查模式组件
6. `Helper` 目录，各种助手类
    1. **[HelperTrait.php](Helper-HelperTrait.md)** 助手类公用 Trait
    2. [ControllerHelper.php](Helper-ControllerHelper.md) 控制器助手类
    3. [ModelHelper.php](Helper-ModelHelper.md) 模型助手类
    4. [BusinessHelper.php](Helper-BusinessHelper.md) 服务助手类
    5. [ViewHelper.php](Helper-ViewHelper.md) 视图助手类
    6. *[AppHelper.php](Helper-AppHelper.md)* 工程应用助手类
7. `HttpServer` 目录
    1. [HttpServer.php](HttpServer-HttpServer.md)  Http 服务器
8. `SingletonEx`目录
    1. **[SingletonEx.php](SingletonEx-SingletonEx.php)**  可变单例 trait
    1. [SimpleReplacer.php](SingletonEx-SimpleReplacer.php)  可选可变单例容器
9. `ThrowOn`目录
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
