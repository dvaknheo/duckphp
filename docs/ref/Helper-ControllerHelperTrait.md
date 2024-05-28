# DuckPhp\Helper\ControllerHelperTrait
[toc]

## 简介

控制器助手Trait

## 方法说明

### 超全局变量
替代同名 `$_GET / $_POST / $_REQUEST / $_COOKIE/ $_SERVER` 。如果没的话返回后面的 $default 默认值。如果 $key 为 null 返回整个数组。
```php
    public static function GET($key = null, $default = null)
    public static function POST($key = null, $default = null)
    public static function REQUEST($key = null, $default = null)
    public static function COOKIE($key = null, $default = null)
    public static function SERVER($key = null, $default = null)
```

### 显示处理
    public static function Render($view, $data = null)
渲染

    public static function Show($data = [], $view = '')
显示视图

    public static function setViewHeadFoot($head_file = null, $foot_file = null)
设置页眉页脚

    public static function assignViewData($key, $value = null)
分配视图变量。 特殊场合使用。

### 配置
    public static function Setting($key)
设置是敏感信息,不存在于版本控制里面。而配置是非敏感。

    public static function Config($file_basename, $key = null, $default = null)
读取配置，从 config/$file_basename.php 里读取配置


### 跳转

    public static function Show302($url)
跳转到相对 url 

    public static function Show404()
报 404，显示后续页面

    public static function ShowJson($ret)
输出 json 结果。

### 路由相关

    public static function Parameter($key = null, $default = null)
和超全局变量类似，获得存储的路由切片数据

    public static function PathInfo()
获取当前 PathInfo

    public static function getRouteCallingMethod()
获取正在调用的路由方法

    public static function getRouteCallingClass()
获取正在调用的路由类


### 系统兼容替换
和系统同名函数(header/setcookie/exit)功能一致，目的是为了兼容不同平台
```php
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    public static function exit($code = 0)
```

### 分页相关
分页器类是通过 DuckPhp\\Component\\Pager 实现的

    public static function Pager($new)
获得或设置当前分页器

    public static function PageNo($new_value = null)
获得或设置当前页码

    public static function PageWindow($new_value = null)
获得或设置当前每页数据条目
    public static function PageHtml($total, $options = [])
获得分页结果 HTML，这里的 $options 是传递给 Pager 类的选项。

### 异常处理

    public static function assignExceptionHandler($classes, $callback = null)
分配异常类回调

    public static function setMultiExceptionHandler(array $classes, $callback)
给多个异常类都帮定到一个回调处理

    public static function setDefaultExceptionHandler($callback)
设置默认的异常处理


### 其他控制器助手方法

    public static function DbCloseAll()
手动关闭数据库

    public static function XpCall($callback, ...$args)
调用 callback, 如果有异常则返回异常对象

    public static function FireEvent($event, ...$args)
触发事件

    public static function OnEvent($event, $callback)
给事件绑定回调

    public static function IsAjax()
判断是否是Ajax 请求

以上就是所有控制器助手方法

### 用户系统相关

    public static function Admin($admin = null)

    public static function AdminId()

    public static function User($user = null)

    public static function UserId()

    public static function Setting($key = null, $default = null)

    public static function Url($url = null)

    public static function Res($url = null)

    public static function Admin($new = null)


    public static function User($new = null)



    public static function Domain($use_scheme = false)



    public static function ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)

    public static function AdminAction()

    public static function UserAction()
    

## 完毕





