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

        'route_map_by_config_name' => '',
路由映射，从配置中读取  route_map_important 和 route_map

        'route_map_auto_extend_method' => true,
路由映射，扩充方法至助手类

## 扩充方法

'assignImportantRoute' => [static::class.'::G','assignImportantRoute'],
'assignRoute' => [static::class.'::G','assignRoute'],
'getRoutes' => [static::class.'::G','getRoutes']
'routeMapNameToRegex'

## 方法

    public static function PrependHook($path_info)
    
    public static function AppendHook($path_info)
    
    protected function initOptions(array $options)
    
    protected function initContext(object $context)
    
    public function compile($pattern_url, $rules = [])
    
    protected function compileMap($map, $namespace_controller)
    
    public function assignRoute($key, $value = null)
    
    public function assignImportantRoute($key, $value = null)
    
    public function getRoutes()
    
    protected function matchRoute($pattern_url, $path_info, &$parameters)
    
    protected function getRouteHandelByMap($routeMap, $path_info)
    
    protected function adjustCallback($callback, $parameters)
    
    public function doHook($path_info, $is_append)
    
    protected function doHookByMap($path_info, $route_map)





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



