# 路由
[toc]
## 相关类

**[Core\\Route](ref/Core-Route.md)**
[Ext\\RouteHookRouteMap](ref/Ext-RouteHookRouteMap.md)
[Ext\\RouteHookRewrite](ref/Ext-RouteHookRewrite.md)
*[Ext\\RouteHookOneFileMode](ref/Ext-RouteHookOneFileMode.md)*
*[Ext\\RouteHookDirectoryMode](ref/RouteHookDirectoryMode.md)*
## 相关选项
'namespace' => 'MY',

	默认的命名空间
'namespace_controller' => 'Controller',

	控制器命名空间， \ 开始的话，则忽略 namespace 选项的设置
'controller_base_class' => null,

	控制器基类, 如果设置，则控制器必须基于这个类
'controller_welcome_class' => 'Main',

	控制器欢迎类， /test 之类的 放在这个相对的类里
'controller_hide_boot_class' => false,

	隐藏默认的路径,你不想让用户访问 /Main/index 也能看到主页的话。
'controller_methtod_for_miss' => '_missing',

	控制器丢失方法，如果有这个方法，则所有动作都在这里处理
'controller_prefix_post' => 'do_',

	POST 方法前缀。 有这个前缀的方法函数处理 post ，没有的话还是同一方法函数处理 所有请求方法
'controller_postfix' => '',

	控制器方法后缀，如果你觉得不好看，比如 indexAction ,testAction ，则自己加上 'Action'

'route_map'



'route_map_important'



## 开始

###  基础路由

DuckPHP 支持很多种 路由方式，最常见最基本的就是文件型路由方式了。

以 PATH_INFO 的 / 为切分，最后一个是方法，前面是命名空间和类名， 当然，还有控制器前缀。

如果只有一个 / 如 /test ，那么就对应到 Main/test。

注意的是，  DuckPHP 不支持 /test/  这样的 url ，最后的 / 需要自己处理。


路由的流程在 DuckPhp\Core\Route 类里run() 方法。

限定的类是在  namespace namespace_controller

根目录的路由会使用 Main 来代替。

为了把 post 和 get 区分， 我们有了 controller_prefix_post 。如果没有 相关方法存在也是没问题的。 这个技巧用于很多需要的情况

### 路由钩子

路由钩子，是在路由运行前后执行的一组钩子。添加的方式是调用 `App::addRouteHook($callback, $position, $once = true)`

$once 是表示同类型钩子，只有一个同名 callback 就够了

position 一共有4个位置
    const HOOK_PREPEND_OUTTER = 'prepend-outter';
    const HOOK_PREPEND_INNER = 'prepend-inner';
    const HOOK_APPPEND_INNER = 'append-inner';
    const HOOK_APPPEND_OUTTER = 'append-outter';

DuckPhp 默认加载了 DuckPhp\\Ext\\RouteHookRouteMap 插件。 实现了路由映射。

其他扩展可能还会有更多的钩子。如果发现“为什么会有这个地址”，去问`核心工程师`吧。

### 路由映射

我们知道，路由重写是经常干的事情，比如  /res/{id} 这样的。

DuckPhp 默认加了扩展插件，添加了两个选项

这些设置在 选项 route_map 和 route_map_important 里设置个映射表.

映射表的 key 为有以下规则

- / 开始的是普通 url

- ~ 开始的是正则 推荐的方法， PHP 有命名参数，会放入

- @ 的是 {} 替换的表达式

value 对应的规则是

- class::method 静态方法

- class@method 动态方法

- class->method 动态方法

- 如果是闭包，直接执行闭包。

例子：

```PHP
<?php declare(strict_types=1);
return [
            '~^user(/page-(?<page>\d+))?$'      => '~user->index',
            '~^user/(?<login>\w+)$'             => '~user->profile',

            '~^api/user/(?<login>\w+)$'         => "~api@profile",
            
            '~^blog/archive/(?<year>\d+)$'      =>"~blog@archive_yearly",
            '~^blog/archive/(?<year>\d+)-(?<month>\d+)(/page(?<page>\d+))?$'    =>"~blog@archive_monthly",
            '~^blog/tag/(?<label>\w+)(/page(?<page>\d+))?$'                     =>"~blog@tag",
            '~^blog/page/(?<slug>\S+)$'                                         =>"~blog@post",
            '~^blog(/(?<id>\d+))?$'                                              =>"~blog@index",
        ];

```

可以用 C::getRoutes();  得到路由表
用 C::getParameters() 获取切片，对地址重写有效。
如果要做权限判断 构造函数里 C::getRouteCallingMethod() 获取当前调用方法。

### 文件模式的路由

### 目录模式的路由
