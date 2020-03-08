# Core\Route

## 简介
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

## 公开方法
    public function __construct()
    public static function RunQuickly(array $options=[], callable $after_init=null)
    public static function URL($url=null)
    public static function Parameters()
    public function _URL($url=null)
    public function _Parameters()
    public function defaultURLHandler($url=null)
    public function init($options=[], $context=null)
    public function setURLHandler($callback)
    public function getURLHandler()
    public function bindServerData($server)
    public function bind($path_info, $request_method='GET')
    public function run()
    public function forceFail()
    public function addRouteHook($callback, $position, $once=true)
    public function add404Handler($callback)
    public function defaulToggleRouteCallback($enable=true)
    public function defaultRunRouteCallback($path_info=null)
    public function defaultGetRouteCallback($path_info)
    public function setPathInfo($path_info)
    public function getRouteError()
    public function getRouteCallingPath()
    public function getRouteCallingClass()
    public function getRouteCallingMethod()
    public function setRouteCallingMethod($calling_method)

## 详解

