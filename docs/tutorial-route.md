# 路由
[toc]

DuckPhp 的路由类比较复杂，也是重点

## 相关类

- [DuckPhp\\Core\\Route](ref/Core-Route.md)
- [DuckPhp\\Component\\RouteHookRouteMap](ref/Component-RouteHookRouteMap.md)
- [DuckPhp\\Component\\RouteHookPathInfoCompat](ref/Component-RouteHookPathInfoCompat.md)

##  默认基础路由

`DuckPhp` 支持很多种 路由方式，默认最常见最基本的就是文件型路由方式了。

以 `PATH_INFO 的` / 为切分，最后一个是方法，前面是命名空间和类名， 当然，还有控制器前缀。

如果只有一个 / 如 `/test` ，那么就对应到 `Main/test`。

注意的是， `DuckPhp` 不支持 /test/  这样的 url ，最后的 / 需要自己处理。

假定 我们的工程命名空间是 ，即 `$options['namespace'] = 'MyProject'`;
默认选项 `$options['namespace_controller'] => 'Controller`';
对应的 类->方法 如下


```
/       => MyProejct\Controller\Main->index
/test   => MyProejct\Controller\Main->test
/a/b    => MyProejct\Controller\a->b
/x/y/z  => MyProejct\Controller\x\y->z

```

和流行框架不同，`Duckphp` 的控制器方法是不用返回任何东西的

你要输出用其他类来输出


路由的流程在 [DuckPhp\Core\Route](ref/Core-Route.md) 类里 `run()` 方法。


根目录的路由会使用 Main（`controller_welcome_class` 选项） 来代替。

为了把 `POST` 和 `GET` 区分， 我们有了 `controller_prefix_post`  选项。如果没有 相关方法存在也是没问题的。 这个技巧用于很多需要的情况


选项介绍
```
'controller_base_class' => '', 限定控制器必须继承基类或实现接口
'controller_class_postfix' => '', 控制器类名后缀，或许你会加上 Action  ，于是就变成了 MyProejct\Controller\MainAction ....
'controller_enable_slash' => false, 激活兼容 URL / 后缀 
'controller_hide_boot_class' => false, 控制器标记，隐藏特别的入口，比如你不想人也从 /Main/index 访问 / MyProejct\Controller\Main->index
'controller_methtod_for_miss' => '_missing', // 如果有这个方法，则定位到的类后，缺失方法的时候调用这个方法
'controller_path_ext' => '', 扩展名，比如你要 .html
'controller_prefix_post' => 'do_', 控制器，POST 方法前缀，用来方便把 POST 方法和其他方法分开，如果没相应类方法则忽略
'controller_stop_g_method' => false, 控制器禁止直接访问 G()方法 ，用于一些技巧
'controller_stop_static_method' => false, 控制器禁止直接访问静态方法 严格禁止访问静态方法，一般不打开
'controller_use_singletonex' => false, 控制器使用单例模式，打开的话全用 G() 方法来访问类
'controller_welcome_class' => 'Main', 控制器默认欢迎类
'namespace' => '',命名空间
'namespace_controller' => 'Controller', 控制器的命名空间 如果以 \\ 开始，则忽略 namespace 选项的配置
'controller_path_prefix' => '', 有时候，你只处理特定开头的 路由
```

## 路由钩子 `核心工程师`

路由钩子，是在路由运行前后执行的一组钩子。添加的方式是调用 `App::addRouteHook($callback, $position, $once = true)`

`$once` 是表示同类型钩子，只有一个同名 callback 就够了

`position` 一共有4个位置
```php
const HOOK_PREPEND_OUTTER = 'prepend-outter';
const HOOK_PREPEND_INNER = 'prepend-inner';
const HOOK_APPPEND_INNER = 'append-inner';
const HOOK_APPPEND_OUTTER = 'append-outter';
````
`DuckPhp` 默认加载了 `DuckPhp\Ext\RouteHookRouteMap` 插件。 实现了路由映射。

其他扩展可能还会有更多的钩子。如果发现“为什么会有这个地址”，去问`核心工程师`吧。

## 路由映射

我们知道，路由重写是经常干的事情，比如  `/res/{id}` 这样的。

`DuckPhp` 默认加了 `DuckPhp\Ext\RouteHookRouteMap` 扩展插件，添加了两个选项

这些设置在 选项 `route_map` 和 `route_map_important` 里设置个映射表.

`route_map_important`  会在普通路由之前执行， `route_map` 在普通路由之后执行。

映射表的 key 为有以下规则

- / 开始的是普通 url

- ^ 开始的是正则 推荐的方法， PHP 有命名参数，会放入

- @ 的是 {} 替换的表达式

value 对应的规则是

- `class::method` 静态方法

- `class@method` 单例动态方法

- `class->method` 动态方法

- 如果是闭包，直接执行闭包。

例子：



```PHP
<?php declare(strict_types=1);
return [
    '^user(/page-(?<page>\d+))?'      => '~user->index',
    '^user/(?<login>\w+)'             => '~user->profile',

    '^api/user/(?<login>\w+)'         => "~api->profile",
    
    '^blog/archive/(?<year>\d+)'                                    =>"~blog->archive_yearly",
    '^blog/archive/(?<year>\d+)-(?<month>\d+)(/page(?<page>\d+))?'  =>"~blog->archive_monthly",
    '^blog/ tag/(?<label>\w+)(/page(?<page>\d+))?'                  =>"~blog->tag",
    '^blog/page/(?<slug>\S+)'                                       =>"~blog->post",
    '^blog(/(?<id>\d+))?'                                           =>"~blog@index",
];

```
@ 开始的为带名字的会编译成 正则表达式  如  `@artcle/{id:w?} => ~(<?id>\w+?)`
（compile 方法

```PHP
<?php declare(strict_types=1);
return [
    '@user(/page-(?<page>\d+))?'      => '~user->index',
    '@user/{login}'             => '~user->profile',
    '@item-{name}-{id:w?} =>'~user->profile',

];

```

如果开启 `route_map_auto_extend_method` 你可以用以下方法得到相应方法

可以用 `C::getRoutes()`  得到路由表
用 `C::getParameters()` 获取切片，对地址重写有效。
如果要做权限判断 构造函数里 `C::getRouteCallingMethod()` 获取当前调用方法。

选项
```
'route_map' => array ( ), //路由映射
'route_map_auto_extend_method' => false, //是否扩充方法至助手类
'route_map_by_config_name' => '', //路由配置名，使用配置模式用路由
'route_map_important' => array ( ), //重要路由映射
```
## 无 `PATH_INFO` 的路由

有时候，你只是做个局部项目，不打算修改 web 服务器配置，你可以使用无 PATH_INFO 的路由。

在选项里取消注释的代码加载以下代码

```php
$options['path_info_compact_action_key' = "_r";
$options['path_info_compact_class_key'] = "";
```
选项说明： `path_info_compact_action_key` 就是 用于路由的 `$_GET` 参数

如果没有 `path_info_compact_class_key` ，直接就是  `?\_r=/test/done` ,  有，就成了 `?\_m=test&_r=done`

`URL ($url) `函数也被接管。 自动替换成相应的实现。

选项
```
'path_info_compact_action_key' => '_r', GET 动作方法名的 key
'path_info_compact_class_key' => '', GET 模式类名的 key
'path_info_compact_enable' => false, 使用 _GET 模拟无 PathInfo 配置
```
## 默认路由生命周期`核心工程师`

`run` 函数。

绑定服务端数据
执行前钩子
执行默认路由
执行后钩子
////

默认路由：获得默认回调 。执行默认回调 `defaultGetRouteCallback`
获取默认类
创建实例 获取要调用的方法 `getMethodToCall` 调用类方法

## 高级内容：制作路由钩子`核心工程师`

...

