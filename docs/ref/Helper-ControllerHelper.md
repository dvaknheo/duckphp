# DuckPhp\Helper\ControllerHelper
[toc]

## 简介

控制器助手类

## 方法
请以教程为准

### 超全局变量
GET($key, $default = null)
POST($key, $default = null)
REQUEST($key, $default = null)
COOKIE($key, $default = null)
显示相关

H($str)
L($str, $args = [])
HL($str, $args = [])
Display($view, $data = null)
Show($data = [], $view = null)
setViewHeadFoot($head_file = null, $foot_file = null)
assignViewData($key, $value = null)

### 配置相关
Setting($key)
Config($key, $file_basename = 'config')
LoadConfig($file_basename)

### 路由相关

URL($url)
Domain()
ExitRedirect($url, $exit = true)
ExitRedirectOutside($url, $exit = true)
ExitRouteTo($url, $exit = true)
Exit404($exit = true)
ExitJson($ret, $exit = true)
getParameters()
getRouteCallingMethod()
setRouteCallingMethod($method)
getPathInfo()

### 系统兼容替换
header($output, bool $replace = true, int $http_response_code = 0)
setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
exit($code = 0)

### 异常处理

assignExceptionHandler($classes, $callback = null)
setMultiExceptionHandler(array $classes, $callback)
setDefaultExceptionHandler($callback)


### 分页相关
- Pager($object = null)
- PageNo()
- PageSize($new_value = null)
- PageHtml($total)

## 详解


## 额外单独扩展的方法

RecordsetUrl [DuckPhp\Ext\Misc::RecordsetUrl](Ext-Misc.md#RecordsetUrl)
	
    DuckPhp\Ext\Misc::RecordsetUrl
RecordsetH
	
    DuckPhp\Ext\Misc::RecordsetH
CallAPI
	
    DuckPhp\Ext\Misc::CallAPI
assignRewrite
	
    DuckPhp\Ext\RouteHookRewrite::G::assignRewrite
getRewrites
	
    DuckPhp\Ext\RouteHookRewrite::G::getRewrites
getRoutes
	
    DuckPhp\Ext\RouteHookRouteMap::G::getRoutes

## 详解

Controller Helper 全是静态方法，调用 App 类的内容。

## 方法索引

