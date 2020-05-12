# Core\Route
[toc]
## 简介
`组件类` `入口类`
很重要的路由类，可以在单独抽出来使用。
## 选项
'namespace' => 'MY',

	默认命名空间为 MY
'namespace_controller' => 'Controller',

	默认子命名空间为 Controller
'controller_base_class' => null,

    限定控制器基类，配合 namespace namespace_controller 选项。
    如果是 \ 开头的则忽略 namespace namespace_controller 选项。
'controller_welcome_class' => 'Main',

    欢迎类
    默认欢迎类是  Main 。

'controller_hide_boot_class' => false,

    隐藏启动的类
'controller_methtod_for_miss' => '_missing',

	方法丢失调用的方法
    如果有这个方法。找不到方法的时候，会进入这个方法
    如果你使用了这个方法，将不会进入 404 。

'controller_prefix_post' => 'do_',

    POST 的方法会在方法名前加前缀 do_
    如果找不到方法名，调用默认方法名。
'controller_postfix' => '',

​	控制器后缀，如果你觉得控制器类的方法不够显眼，你可以设置成其他的
## 方法

### 主流程方法

### 公开方法
public function __construct()

    空构造函数
public function init(array $options, object $context = null)

    初始化
public static function RunQuickly(array $options=[], callable $after_init=null)

    快速方法，等同于 init([])->run();
public function bindServerData($server)

    绑定 $_SERVER 数组
public function bind($path_info, $request_method = 'GET')

    绑定 PATH_INFO
public function run()

    运行
public function forceFail()

    强制为失败
public function addRouteHook($callback, $position, $once = true)
    
    添加钩子
public function add404Handler($callback)

    添加 404 回调
public function defaulToggleRouteCallback($enable = true)

    切换默认的路由回调
public function defaultRunRouteCallback($path_info = null)

    运行默认的路由回调
public function defaultGetRouteCallback($path_info)

    获得默认的路由回调
protected function beforeRun()

    用于重写，在回调前执行
protected function getRunResult()
    获得运行结果
protected function createControllerObject($full_class)
    创建控制器对象
protected function getMethodToCall($object, $method)

    获得回调方法

### URL 相关

public static function URL($url=null)

    获得 URL
public function _URL($url = null)

    URL 的实现函数
public function defaultURLHandler($url = null)
    
    默认的 URL 函数，
public function setURLHandler($callback)

    设置 URL 回调函数
    
public function getURLHandler()

    获得 URL 回调函数

### 辅助信息方法

public function getPathInfo()

    获得 PATH_INFO
public function setPathInfo($path_info)

    设置 PATH_INFO
public function getParameters()

    获得 数组
public function setParameters($parameters)
public function getRouteCallingPath()

public function getRouteCallingClass()

    获得当前路由调用的类名
public function getRouteCallingMethod()
public function setRouteCallingMethod($calling_method)

    设置当前路由

public function getRouteError()

获得

## 详解

## 方法索引

    public function __construct()
    public static function RunQuickly(array $options = [], callable $after_init = null)
    public static function URL($url = null)
    public function _URL($url = null)
    public function defaultURLHandler($url = null)
    public function init(array $options, object $context = null)
    public function setURLHandler($callback)
    public function getURLHandler()
    public function bindServerData($server)
    public function bind($path_info, $request_method = 'GET')
    protected function beforeRun()
    public function run()
    protected function getRunResult()
    public function forceFail()
    public function addRouteHook($callback, $position, $once = true)
    public function add404Handler($callback)
    public function defaulToggleRouteCallback($enable = true)
    public function defaultRunRouteCallback($path_info = null)
    public function defaultGetRouteCallback($path_info)
    protected function createControllerObject($full_class)
    protected function getMethodToCall($object, $method)
    public function getPathInfo()
    public function setPathInfo($path_info)
    public function getParameters()
    public function setParameters($parameters)
    public function getRouteError()
    public function getRouteCallingPath()
    public function getRouteCallingClass()
    public function getRouteCallingMethod()
    public function setRouteCallingMethod($calling_method)
    
##### 

### Core\Route

DuckPhp\Core\Route 这个类可以单独拿出来做路由用。



##### 示例

这是一个单用 Route 组件的例子

```php
<?php
use DuckPhp\Core\Route;
require(__DIR__.'/vendor/autoload.php');

class Main
{
    public function index()
    {
        var_dump(DATE(DATE_ATOM));
    }
    public function i()
    {
        phpinfo();
    }
}
$options=[
    'namespace_controller'=>'\\',
];
$flag=Route::RunQuickly($options);
if(!$flag){
    header(404);
    echo "404!";
}

```

##### 钩挂路由流程指南

    如果你对默认的文件路由不满意，可以安插自己的钩子。
    $route->addRouteHook($callback, $append=true, $outter=true, $once=true);
    其中， $callback 为你的钩子函数，符合 callback(string $path_info):bool
    当你返回 true 的时候，表示成功。 将不再执行后面的函数。
    一共有4个钩挂点可用。 $append,$outter。
    defaultRunRouteCallback($path_info);  给做了默认榜样。
    defaultGetRouteCallback($path_info); 则是获得，但不处理调用。
    如果你在前面的，想禁止默认路由函数，可以用 defaultToggleRouteCallback(false);
    
    add404Handle() 是默认用于后处理的版本。

##### URL 输出地址重写指南


## Tip bind() 函数和 bindServerData 区别
