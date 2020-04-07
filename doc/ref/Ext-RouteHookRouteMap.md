# Ext\RouteHookRouteMap

## 简介

`路由钩子` `组件类` `默认使用` 自定义路由的扩展

## 选项

+ 'route_map_important'=>[],

    在默认路由前执行的路由映射
+ 'route_map'=>[]

  在默认路由失败后执行的路由映射

## 扩充方法

'assignImportantRoute' => [static::class.'::G','assignImportantRoute'],
'assignRoute' => [static::class.'::G','assignRoute'],
'getRoutes' => [static::class.'::G','getRoutes']
'routeMapNameToRegex'

## 公开方法


## 详解
~  开始的为正则
@ 开始的为带名字的

### RouteHookRouteMap

默认开启,实现了路由映射功能

如果是 * 结尾，那么把后续的按 / 切入 parameters
route_map key 如果是 ~ 开头的，表示正则
否则是普通的 path_info 匹配。

支持 'Class->Method' 和 'Class@Method'  表示创建对象，执行动态方法。

parameters 

#### 方法
assignRoute($route,$callback); 
    是 C::assignRoute 和 App::assignRoute 的实现。
getRoutes()
    dump  route_map 的内容。
    public function __construct()
    public static function PrependHook($path_info)
    public static function AppendHook($path_info)
    public function init(array $options, object $context = null)
    public function compile($pattern_url, $rules = [])
    public function assignRoute($key, $value = null)
    public function assignImportantRoute($key, $value = null)
    public function getRoutes()
    protected function matchRoute($pattern_url, $path_info, &$parameters)
    protected function getRouteHandelByMap($routeMap, $path_info, &$parameters)
    protected function adjustCallback($callback)
    public function doHook($path_info, $is_append)
    protected function doHookByMap($path_info, $route_map)