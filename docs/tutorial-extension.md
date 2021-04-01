# 扩展教程
[toc]

## 使用 DuckPhp 的扩展

DuckPhp 扩展的加载是通过选项里添加

$options['ext']数组实现的

    扩展映射 ,$ext_class => $my_options。
    $ext_class 为扩展的类名，如果找不到扩展类则不启用。
    $ext_class 满足组件接口。在初始化的时候会被调用。
    $ext_class->init(array $options,$context=null);
    
    如果 $my_options。 为  false 则不启用，
    如果 $my_options。 为 true ，则会把当前 $options 传递进去。
    如果 $my_options。 为 字符串 ，则会映射到 $optioins[$my_options]。

DuckPhp/Core 的其他组件如 Configer, Route, View, AutoLoader 默认都在这调用

## 编写扩展

假如你要做个自己的扩展 MyExtention , 你的类只要能实现这样的调用。

MyExtends::G()->init(array $options, $contetxt=null);

非强制实现 DuckPhp\\Core\\ComponentInterface();

一般要提供 MyExtends::G()->options 保存自己的 选项。

### ComponentBase 基类 和 ComponentInterface 接口

ComponentBase 帮你实现了 这些东西，
你只要写自己的 $options 和 重写 initOptions()  initContext(object $context) 就行了。 默认父类这两个是空类。


### 编写扩展的技巧

extendComponents ，如果你要把你的类给助手类使用。

['A','M','V','C','B'] 都是各助手类的名称缩写。


```
$context->extendComponents(
    [
        'assignImportantRoute' => [static::class.'::G','assignImportantRoute'],
        'assignRoute' => [static::class.'::G','assignRoute'],
        'routeMapNameToRegex' => [static::class.'::G','routeMapNameToRegex'],
    ],
    ['A']
);
```

## 默认扩展



所有的 DuckPhp 自带扩展 可以在 [参考文档](ref/index.md) 里按字母顺序查看


默认没启用的扩展列表如下


### 按字母顺序
* [CallableView](ref/Ext-CallableView.md) 函数方式的视图
* [EmptyView](ref/Ext-EmptyView.md) 替换 View ，效果是空，用来收集输出。
* [JsonRpcExt](ref/Ext-JsonRpcExt.md)
* [ExceptionWrapper](ref/Ext-ExceptionWrapper.md)
* [JsonView](ref/Ext-JsonView.md)
* [Misc](ref/Ext-Misc.md) 一些通用方法
* [MyFacadesAutoLoader](ref/Ext-MyFacadesAutoLoader.md) Facade 门面
* [MyMiddlewareManager](ref/Ext-MyMiddlewareManager.md)
* [RedisCache](ref/Ext-RedisCache.md)
* [RedisManager](ref/Ext-RedisManager.md)
* [RouteHookApiServer](ref/Ext-RouteHookApiServer.md)
* [RouteHookRewrite](ref/Ext-RouteHookRewrite.md)
* [RouteHookDirectoryMode](ref/Ext-RouteHookDirectoryMode.md)
* [StaticReplacer](ref/Ext-StaticReplacer.md)
* [StrictCheck](ref/Ext-StrictCheck.md) 严格检查

扩展支持的文件

### CallableView
CallbableView 是用来代替 View 的一个扩展
把 View 的视图文件都替换成了函数方法

### EmptyView
EmptyView 替换 View ，效果是空，用来收集输出。



### RedisManager  Redis 的一些扩展


### JsonRpcExt



## Ext 下面非扩展组件但有用的类

HookChain


