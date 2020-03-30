# Core\Route

## 简介
`组件类` `入口类`
很重要的路由类，可以在单独抽出来使用。
## 选项
'namespace' => 'MY',
	默认命名空间为 MY
'namespace_controller' => 'Controller',
	默认子命名空间为 Controller
'controller_base_class' => null,
	必须的基类
'controller_welcome_class' => 'Main',
	欢迎类
'controller_hide_boot_class' => false,
	隐藏启动的类
'controller_methtod_for_miss' => '_missing',
	方法丢失调用的方法
'controller_prefix_post' => 'do_',
	post 模式下的类
'controller_postfix' => '',

​	控制器后缀

## 方法
### 公开方法
public function __construct()

    空构造函数
public function init(array $options, object $context = null)

    初始化
public static function RunQuickly(array $options=[], callable $after_init=null)

    快速运行
public static function URL($url=null)

    获得 URL

public function _URL($url = null)

    URL 的实现函数
public function defaultURLHandler($url = null)
    
    默认的 URL 函数，
public function setURLHandler($callback)

    
public function getURLHandler()

public function bindServerData($server)
public function bind($path_info, $request_method = 'GET')

public function run()
public function forceFail()
public function addRouteHook($callback, $position, $once = true)
public function add404Handler($callback)

public function defaulToggleRouteCallback($enable = true)
public function defaultRunRouteCallback($path_info = null)
public function defaultGetRouteCallback($path_info)


protected function beforeRun()
protected function getRunResult()
protected function createControllerObject($full_class)
protected function getMethodToCall($object, $method)

public function getRouteError()

获得


### Getter/Setter

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

DuckPHP\Core\Route 这个类可以单独拿出来做路由用。

##### 选项

```php
$options=[
    'namespace'=>'MY',
    'namespace_controller'=>'Controller',
    'controller_base_class'=>null,
    'controller_welcome_class'=>'Main',
    'controller_hide_boot_class'=>false,
    'controller_methtod_for_miss'=>'_missing',
    'controller_prefix_post'=>'do_',
    'controller_postfix'=>''
]
```

'controller_base_class'=>null,
    

    限定控制器基类，配合 namespace namespace_controller 选项。
    如果是 \ 开头的则忽略 namespace namespace_controller 选项。

'controller_prefix_post'=>'do_',

    POST 的方法会在方法名前加前缀 do_
    如果找不到方法名，调用默认方法名。

'controller_welcome_class'=>'Main',

    默认欢迎类是  Main 。

'controller_methtod_for_miss'=>'_missing',
    

    如果有这个方法。找不到方法的时候，会进入这个方法
    如果你使用了这个方法，将不会进入 404 。

'controller_prefix_post'=>'do_'

    拆分 POST 方法到 do_ 开头的方法。

'controller_postfix'=>'',

     控制器后缀，如果你觉得控制器类不够显眼，你可以设置成Controller

##### 示例

这是一个单用 Route 组件的例子

```php
<?php
use DuckPHP\Core\Route;
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

##### 静态方法

public static function RunQuickly(array $options=[], callable $after_init=null)

    快速方法，等同于 init()->run();

public static function URL($url=null)

    获取某个 相对 URL的绝对 URL 地址

public static function getParameters()

    获得切片数组。

##### 公开动态方法

    public function _URL($url=null)
    public function _Parameters()
    public function defaultURLHandler($url=null)
    public function setURLHandler($callback)
    public function getURLHandler()

##### 主要流程方法。

    public function bindServerData($server)
    public function bind($path_info, $request_method='GET')
    
    protected function beforeRun()
    public function run()
    public function defaultRunRouteCallback($path_info=null)
    public function defaultGetRouteCallback($path_info)
    public function defaultToggleRouteCallback($enable)
    
    public function addRouteHook($callback, $append=true, $outter=true, $once=true)
    public function add404Handler($callback)
    
    protected function createControllerObject($full_class)
    protected function getMethodToCall($obj, $method)

##### 辅助信息方法

    public function getRouteCallingPath()
    public function getRouteCallingClass()
    public function getRouteCallingMethod()
    public function setRouteCallingMethod($calling_method)

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
