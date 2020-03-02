# Ext\RouteHookRouteMap

## 简介

## 选项

## 公开方法


## 详解

### RouteHookRouteMap

默认开启,实现了路由映射功能
#### 选项
```php
$options=[
   'route_map'=>[],
]
```
如果是 * 结尾，那么把后续的按 / 切入 parameters
route_map key 如果是 ~ 开头的，表示正则
否则是普通的 path_info 匹配。

支持 'Class->Method' 和 'Class@Method'  表示创建对象，执行动态方法。
你可以 
parameters 

#### 方法
assignRoute($route,$callback); 
    是 C::assignRoute 和 App::assignRoute 的实现。
getRoutes()
    dump  route_map 的内容。