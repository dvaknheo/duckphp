# DuckPhp\Component\RouteHookRouteMap
[toc]

## 简介

`路由钩子` `组件类 自定义路由的扩展

## 选项
全部选项

        'route_map_important' => [],
路由映射，在默认路由前执行的路由映射

        'route_map' => [],
路由映射，在默认路由失败后执行的路由映射


        'controller_url_prefix' => '',
路由生效浅醉


## 方法

    public static function PrependHook($path_info)
    public static function AppendHook($path_info)
    public function doHook($path_info, $is_append)
路由钩子处理

    protected function initOptions(array $options)    
    protected function initContext(object $context)
初始化

    
    public function assignRoute($key, $value = null)
    
    public function assignImportantRoute($key, $value = null)
    
    public function getRouteMaps()
    
    public function compile($pattern_url, $rules = [])


    protected function matchRoute($pattern_url, $path_info, &$parameters)
    
    protected function getRouteHandelByMap($routeMap, $path_info)
    
    protected function adjustCallback($callback, $parameters)
    
    protected function compileMap($map, $namespace_controller)
    
    protected function doHookByMap($path_info, $route_map)

## 路由映射

我们知道，路由重写是经常干的事情，比如  `/res/{id}` 这样的。

`DuckPhp` 默认加了 `DuckPhp\Component\RouteHookRouteMap` 扩展插件，添加了两个选项

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


## 详解
key 的规则
~  开始的为正则  ~abc

@ 开始的为带名字的会编译成 正则表达式  如  @artcle/{id:w?} => ~<? （compile 方法

### RouteHookRouteMap

默认开启,实现了路由映射功能

如果是 * 结尾，那么把后续的按 / 切入 parameters
route_map key 如果是 ~ 开头的，表示正则
否则是普通的 path_info 匹配。

支持 'Class->Method' 和 'Class@Method'  表示创建对象，执行动态方法。
Class@Method => Class::G()->Method

assignRoute($route,$callback=null)

    给路由加回调。
    单个 assign($key,$value) 和多个 assign($assoc)；
    关于回调模式的路由。详细情况看之前介绍
    和在 options['route'] 添加数据一样
parameters 

#### 方法
assignRoute($route,$callback); 
    是 C::assignRoute 和 App::assignRoute 的实现。
getRoutes()
    dump  route_map 的内容。




