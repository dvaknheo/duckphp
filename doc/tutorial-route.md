# 路由
## 相关类

**[Core\Route](ref/Core-Route.md)**

[Ext\RouteHookRewrite](ref/Ext-RouteHookRewrite.md)

[Ext\RouteHookRouteMap](ref/Ext-RouteHookRouteMap.md)

*[Ext\RouteHookOneFileMode](ref/Ext-RouteHookOneFileMode.md)*

*[Ext\RouteHookDirectoryMode](ref/RouteHookDirectoryMode.md)*

## 开始

路由的流程在 DuckPhp\Core\Route 类里
run() 方法。


duckphp 默认加载了 routemap 和 routerewrite 插件。

路由钩子

路由映射

路由重写

自定义路由

单一文件模式的路由

文件模式的路由


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
