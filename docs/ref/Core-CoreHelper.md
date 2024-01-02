# DuckPhp\Core\CoreHelper
[toc]
## 简介
这个组件是常用函数集合 两个下划线开始的方法，都是调用这里的实现

### 内置 trait Core_Helper
内置 trait Core_Helper 用于各种助手方法

#### 跳转
```php
    public static function ExitJson($ret, $exit = true)
    public static function ExitRedirect($url, $exit = true)
    public static function ExitRedirectOutside($url, $exit = true)
    public static function ExitRouteTo($url, $exit = true)
    public static function Exit404($exit = true)
```
#### 字符串处理
```php
    public static function H($str)
    public static function L($str, $args = [])
    public static function Hl($str, $args = [])
    public static function Json($data)
    
    public static function Render($view, $data = null)
```
#### 调试相关
```php
    public static function var_dump(...$args)
    public static function Platform()
    public static function IsDebug()
    public static function IsRealDebug()
    public static function TraceDump()
    public static function Logger($object = null)
    public static function DebugLog($message, array $context = array())
    public static function XpCall($callback, ...$args)
    public static function CheckException($exception_class, $message, $code = 0)
```
#### SQL 相关
```php
    public static function SqlForPager($sql, $pageNo, $pageSize = 10)
    public static function SqlForCountSimply($sql)
```
#### 分页
分页器默认没加载
```php
    public static function Pager($object = null)
    public static function PageNo($new_value = null)
    public static function PageSize($new_value = null)
    public static function PageHtml($total, $options = [])
```
#### 其他

    public static function Cache($object = null)
缓存对象

    public static function IsAjax()
检查是否是 Ajax 请求


### 内置 trait  Core_NotImplemented
内置 trait Core_NotImplemented DuckPhp\Core\App 没实现，但 DuckPhp\DuckPhp 类实现的的方法。数据库和事件系统
```php
    public static function Db($tag = null)
    public static function DbCloseAll()
    public static function DbForWrite()
    public static function DbForRead()
    public static function Event()
    public static function FireEvent($event, ...$args)
    public static function OnEvent($event, $callback)
```

### 内置 trait Core_Glue

内置 trait Core_Glue 用于粘合其他类
大写的方法是复制相关类的静态方法。小写是动态方法
以下按粘合的类区分：

#### 来自RuntimeState
```php
    public static function isInException()
    public static function isRunning()
```
#### 来自 Configer
```php
    public static function Setting($key)
    public static function Config($key, $default = null, $file_basename = 'config')
```
#### 来自 AutoLoader
来自 AutoLoader 的两个方法，主要用于没把 composer 作为加载器使用的情况
```php 
    public static function assignPathNamespace($path, $namespace = null)
    public static function runAutoLoader()
```
#### 来自 Route
来自Route 的方法比较多。重点掌握

    public static function Route($replacement_object = null)
获得 Route 对象

    public static function Url($url = null)
获得 url

    public static function Res($url = null)
获得资源 url 。默认下资源 url 等同于 url

    public static function Domain($use_scheme = false)
获得域名

    public static function Parameter($key = null, $default = null)
获取存储的 paramters 。rewrite 之后会保存在这。

    public static function PathInfo()
获取 PathInfo

    public static function replaceController($old_class, $new_class)
单例模式，替换控制器类

##

    public static function H($str)

    public static function L($str, $args = [])

    public static function Hl($str, $args = [])

    public static function Json($data)

    public static function Url($url = null)

    public static function Domain($use_scheme = false)

    public static function Res($url = null)

    public static function Display($view, $data = null)

    public static function var_dump(...$args)

    public static function VarLog($var)

    public static function TraceDump()

    public static function DebugLog($message, array $context = array())

    public static function Logger($object = null)

    public static function IsDebug()

    public static function IsRealDebug()

    public static function Platform()

    public static function IsAjax()

    public static function ExitJson($ret, $exit = true)

    public static function ExitRedirect($url, $exit = true)

    public static function ExitRedirectOutside($url, $exit = true)

    public static function ExitRouteTo($url, $exit = true)

    public static function XpCall($callback, ...$args)

    public static function SqlForPager($sql, $page_no, $page_size = 10)

    public static function SqlForCountSimply($sql)

    public static function ThrowByFlag($exception, $flag, $message, $code = 0)

    public function _H(&$str)

    public function _L($str, $args = [])

    public function _Hl($str, $args)

    public function _Json($data)

    public function _VarLog($var)

    public function _var_dump(...$args)

    public function _TraceDump()

    public function _DebugLog($message, array $context = array())

    public function _IsDebug()

    public function _IsRealDebug()

    public function _Platform()

    public function _IsAjax()

    public static function Exit404($exit = true)

    public function _ExitJson($ret, $exit = true)

    public function _ExitRedirect($url, $exit = true)

    public function _ExitRedirectOutside($url, $exit = true)

    public function _XpCall($callback, ...$args)

    public function _SqlForPager($sql, $page_no, $page_size = 10)

    public function _SqlForCountSimply($sql)

    public function _ThrowByFlag($exception, $flag, $message, $code = 0)

    public static function SqlForPager($sql, $page_no, $page_size = 10)

    public static function SqlForCountSimply($sql)

    public static function PhaseCall($phase, $callback, ...$args)

    public function _PhaseCall($phase, $callback, ...$args)


    public static function BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)

    public static function ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)

    public function _BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)

    public function _ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)

