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
