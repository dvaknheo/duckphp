# 路由
[toc]
## 相关类

**[Core\\Route](ref/Core-Route.md)**

[Ext\\RouteHookRewrite](ref/Ext-RouteHookRewrite.md)

[Ext\\RouteHookRouteMap](ref/Ext-RouteHookRouteMap.md)

*[Ext\\RouteHookOneFileMode](ref/Ext-RouteHookOneFileMode.md)*

*[Ext\\RouteHookDirectoryMode](ref/RouteHookDirectoryMode.md)*
## 相关选项
'namespace' => 'MY',

	默认的命名空间
'namespace_controller' => 'Controller',

	控制器命名空间
'controller_base_class' => null,

	控制器基类
'controller_welcome_class' => 'Main',

	控制器欢迎类
'controller_hide_boot_class' => false,

	隐藏默认的路径
'controller_methtod_for_miss' => '_missing',

	控制器丢失方法
'controller_prefix_post' => 'do_',

	POST 方法前缀
'controller_postfix' => '',

	控制器方法后缀

'route_map'
'route_map_important'

## 开始

###  文件型路由

DuckPHP 很传统。
路由的流程在 DuckPhp\Core\Route 类里run() 方法。

限定的类是在  namespace namespace_controller

根目录的路由会使用 Main 来代替。

为了把 post 和 get 区分， 我们有了 controller_prefix_post 。如果没有 相关方法存在也是没问题的。 这个技巧用于很多需要的情况


### 路由钩子
路由钩子，是在路由运行前后执行的一组钩子。通过

addRouteHook($callback, $position, $once = true)
添加
$once 是表示同类型钩子，只有一个同名 callback 就够了

position 一共有4个位置
    const HOOK_PREPEND_OUTTER = 'prepend-outter';
    const HOOK_PREPEND_INNER = 'prepend-inner';
    const HOOK_APPPEND_INNER = 'append-inner';
    const HOOK_APPPEND_OUTTER = 'append-outter';


duckphp 默认加载了 routemap 和 routerewrite 插件。


### 路由映射

### 路由重写

### 自定义路由

### 单一文件模式的路由

### 目录模式的路由


用 C::getParameters() 获取切片，对地址重写有效。
如果要做权限判断 构造函数里 C::getRouteCallingMethod() 获取当前调用方法。

用 C::getRewrites() 和 C::getRoutes(); 查看 rewrite 表，和 路由表。

assignRewrite($old_url,$new_url=null)

    支持单个 assign($key,$value) 和多个 assign($assoc)
    rewrite  重写 path_info
    不区分 request method , 重写后可以用 ? query 参数
    ~ 开始表示是正则 ,为了简单用 / 代替普通正则的 \/
    替换的url ，用 $1 $2 表示参数

assignRoute($route,$callback=null)

    给路由加回调。
    单个 assign($key,$value) 和多个 assign($assoc)；
    关于回调模式的路由。详细情况看之前介绍
    和在 options['route'] 添加数据一样
