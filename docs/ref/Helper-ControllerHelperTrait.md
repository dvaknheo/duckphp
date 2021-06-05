# DuckPhp\Helper\ControllerHelperTrait
[toc]

## 简介

ControllerHelper 绑定了 ControllerHelperTrait

ControllerHelper 绑定了 [DuckPhp\Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md) ，ControllerHelperTrait


### 超全局变量
替代同名 $\_GET / $\_POST /$\_REQUEST /$\_COOKIE/$\_SERVER 。如果没的话返回后面的默认值。如果 $key 为 null 返回整个数组。
```php
    public static function GET($key = null, $default = null)
    public static function POST($key = null, $default = null)
    public static function REQUEST($key = null, $default = null)
    public static function COOKIE($key = null, $default = null)
    public static function SERVER($key, $default = null)
```
### 字符串处理

    public static function H($str)
\_\_h()； HTML 编码

    public static function Json($data)
\_\_json()； Json 编码

    public static function L($str, $args = [])
\_\_l() 语言处理函数，后面的关联数组替换 '{$key}'
    
    public static function Hl($str, $args = [])
\_\_hl() 对语言处理后进行 HTML 编码
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

    public static function Config($key, $file_basename = 'config')
读取配置，从 config/$file_basename.php 里读取配置

    public static function LoadConfig($file_basename)
载入配置,获得配置项目。

### 跳转

跳转方法的 $exit 为 true 则附加 exit()

    public static function ExitRedirect($url, $exit = true)
跳转到站内 Url

    public static function ExitRedirectOutside($url, $exit = true)
跳转到站外 Url 。这两个函数分开是为了安全起见

    public static function ExitRouteTo($url, $exit = true)
跳转到相对 url 

    public static function Exit404($exit = true)
报 404，显示后续页面

    public static function ExitJson($ret, $exit = true)
输出 json 结果。

### 路由相关

    public static function Url($url)
获得相对 url 地址

    public static function Domain($use_scheme = false)
获得带协议的域名

    public static function Parameter($key, $default = null)
和超全局变量类似，获得存储的数据

    public static function getPathInfo()
获取当前 PathInfo
    public static function getRouteCallingMethod()
获取正在调用的路由方法，构造函数里使用。

    public static function setRouteCallingMethod($method)
设置调用的路由方法， 强行改变 view 的默认行为时候用。

### 系统兼容替换
和系统同名函数(header/setcookie/exit)功能一致，目的是为了兼容性
```php
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    public static function exit($code = 0)
```

### 分页相关
分页器类是通过 DuckPhp\\Component\\Pager 实现的

    public static function PageNo($new_value = null)
获得或设置当前页码

    public static function PageSize($new_value = null)
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
常用的一个操作：
```php
<?php
    //旧：
    public function login_old()
    {
        $error = '';
        $post = C::POST();
        if($post){
            try{
                $admin = AdminService::G()->login($post);
                SessionService::G()->setCurrentAdmin($admin,$post['remember']);
                C::ExitRouteTo('profile/index');
                return;
            }catch(\Throwable $ex){
                $error = $ex->getMessage();
            }
        }
        C::Show(get_defined_vars(), 'login');
    }
    //新：
    public function login()
    {
        C::Show(get_defined_vars(), 'login');
    }
    public function do_login()
    {
        C::setDefaultExceptionHandler(function($ex){
            $error = $ex->getMessage();
            C::assignViewData(['error'=>$error]);
            $this->login();
        });
        $post = C::POST();
        $admin = AdminService::G()->login($post);
        SessionService::G()->setCurrentAdmin($admin,$post['remember']);
        C::ExitRouteTo('profile/index');
    }
```

### 其他控制器助手方法

    public static function DbCloseAll()
手动关闭数据库

    public static function XpCall($callback, ...$args)
调用 callback, 如果有异常则返回异常对象

    public static function FireEvent($event, ...$args)
触发事件

    public static function OnEvent($event, $callback)
给事件绑定回调

    public static function dumpAllRouteHooksAsString()
打印所有路由钩子，调试用

    public static function IsAjax()
判断是否是Ajax 请求

    public static function CheckRunningController($self, $static)
用于基类里判断是否被直接调用。参见 App 里相关文档

以上就是所有控制器助手方法
