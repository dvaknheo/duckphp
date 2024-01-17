# DuckPhp\Core\Route
[toc]

## 简介

`组件类` `入口类`
很重要的路由类，可以在单独抽出来使用。

## 选项
所有配置选项如下

        'namespace' => '',
默认命名空间为空

        'namespace_controller' => 'Controller',
默认子命名空间为 Controller
如果是 \ 开头的则忽略 `namespace` 选项。

        'controller_base_class' => '',
控制器，基类

        'controller_hide_boot_class' => true,
控制器，隐藏启动的类

        'controller_prefix_post' => 'do_',
控制器，POST 的方法会在方法名前加前缀 do_
如果找不到方法名，调用默认方法名。

        'controller_class_postfix' => 'Controller',
控制器，控制器类名后缀

        'controller_enable_slash' => false,
控制器，允许结尾的 /

        'controller_path_prefix' => '',
控制器，路由的前缀，只处理限定前缀的 PATH_INFO

        'controller_path_ext' => '',
控制器，后缀,如 .html

        'controller_stop_static_method' => true,
控制器，禁止直接访问控制器静态方法

        'controller_strict_mode' => true,
控制器，严格模式，区分大小写

        'controller_class_map' => [],
控制器，类映射，用于替换控制器，类


        'controller_url_prefix' => '',
控制器，资源文件

        'controller_resource_prefix' => '',
控制器，资源文件前缀

        'controller_welcome_class' => 'Main',

        'controller_welcome_class_visible' => false,

        'controller_welcome_method' => 'index',

        'controller_class_base' => '',


        'controller_method_prefix' => 'action_',

        'controller_prefix_post' => 'do_', //TODO remove it

        'controller_class_adjust' => '',

## 公开方法

### 主流程方法
这里是主要流程的方法

    public static function RunQuickly(array $options = [], callable $after_init = null)
快速方法，等同于 init([])->run();

    public function reset()

重置，初始化之后的重置

    public function run()
运行

### 扩展和钩子
////

    public function addRouteHook($callback, $position = 'append-outter', $once = true)

添加钩子


    public function defaulToggleRouteCallback($enable = true)
切换默认的路由回调

    public function defaultRunRouteCallback($path_info = null)
运行默认的路由回调

    public function defaultGetRouteCallback($path_info)

运行默认的路由回调

    public function replaceController($old_class, $new_class)
替换控制器

    public static function Route()
返回单例，用于 DuckPhp/Route 双兼容给路由钩子使用

    public function forceFail()

强制为失败，用于路由钩子

    public function runtime()
返回保存运行期数据的类


### URL 相关
这里是路由相关的

    public static function Url($url = null)
    public function _Url($url = null)
获得 URL

    public static function Res($url = null)
    public function _Res($url = null)
获得资源地址

    public static function Domain($use_scheme = false)
    public function _Domain($use_scheme = false)
获得的域名

    public function defaultUrlHandler($url = null)
默认的 URL 函数

    public function setUrlHandler($callback)
    public function getUrlHandler()
设置/获得 URL 回调函数

### 辅助方法

    protected function adjustMethod($method, $ref)

    protected function getCallbackFromClassAndMethod($full_class, $method, $path_info)

    protected function adjustClassBaseName($path_info)

其他辅助方法

    public function getRouteError()
获取路由错误信息

    public function setParameters($parameters)
设置 Parameter 数组

    public static function Parameter($key = null, $default = null)
    public function _Parameter($key = null, $default = null)
读取 Parameter ， Parameter 用于 Url 重构之类

    public static function PathInfo($path_info = null)
    public function _PathInfo($path_info = null)
    protected function getPathInfo()
    protected function setPathInfo($path_info)
获取和设置 PathInfo

    public function getRouteCallingPath()
获取调用中的路径

    public function getRouteCallingClass()

获得当前路由调用的类名

    public function getRouteCallingMethod()
    public function setRouteCallingMethod($calling_method)
设置当前路由

    public function dumpAllRouteHooksAsString()
简单 dump 所有钩子

### 其他方法

    public function bind($path_info, $request_method = 'GET')


 绑定一个path_info和方法   
    public function getControllerNamespacePrefix()
RouteHookRouteMap 用到 获取控制器命名空间

### 内部方法
以下是内部方法

    protected function initOptions(array $options)
重写了初始化选项


    protected function getRunResult()
获得运行结果

    protected function createControllerObject($full_class)
重写用，创建控制器对象

    protected function pathToClassAndMethod($path_info)
重写用，createControllerObject 会调用这个

    protected function getMethodToCall($object, $method)
重写用，获得回调方法

    protected function getUrlBasePath()
获得 URL 基本地址

## 说明

### 示例

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

### 钩挂路由流程指南

    如果你对默认的文件路由不满意，可以安插自己的钩子。
    $route->addRouteHook($callback, $append=true, $outter=true, $once=true);
    其中， $callback 为你的钩子函数，符合 callback(string $path_info):bool
    当你返回 true 的时候，表示成功。 将不再执行后面的函数。
    一共有4个钩挂点可用。 $append,$outter。
    defaultRunRouteCallback($path_info);  给做了默认榜样。
    defaultGetRouteCallback($path_info); 则是获得，但不处理调用。
    如果你在前面的，想禁止默认路由函数，可以用 defaultToggleRouteCallback(false);
    
    add404Handle() 是默认用于后处理的版本。

### URL 输出地址重写指南



## 文档信息
修订版本：

修订时间：






