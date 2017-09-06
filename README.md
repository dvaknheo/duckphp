# DNMVCS
## DNMVCS 是什么
一个 PHP Web 简单框架 比通常的Model Controller View 多了 Service
拟补了 常见 Web 框架少的缺层 

## DNMVCS 做了什么
简单可扩展灵活的路由方式。
简单的数据库类
扩展接管默认错误出来
简单的加载类
简单的额配置类
所有这些仅仅是在主类里耦合。

## DNMVCS 不做什么
ORM ，和各种屏蔽 sql 的行为
模板引擎，PHP本身就是模板引擎
Widget ， 和 MVC 分离违背
系统行为 ，接管替代默认的POST，GET 。

## DNMVCS 如何使用
model 按数据库表走
view 按页面走
controller 按url入口走
service 按业务走

controller 调用 view 和service
service 调用 model 和其他第三方代码
model 只实现和当前表相关的操作
controller ,service ,model 都可能抛出异常

如果 service 相互调用怎么办?
添加后缀为 LibService 用于 service 共享调用，不对外，如MyLibService

如果跨表怎么办?
两种解决方案
1 在主表里附加
2 添加后缀为 XModel 用于表示这个 Model 是多个表的，如 MyXModel。

库 service  和 联表 model 并没有单独目录。
## DNMVCS 的目录结构

## DNMVCS 的各个类说明
class DNSingleton
各个类基本都要继承的类。
        public static function G($url=null)
如果没有这个 G 方法 你可能会怎么写代码：
(new MyClass())->foo();
继承了 DNSingleton 后，这么写
MyClass::G()->foo();

另一个隐藏功能：
MyBaseClass::G(new MyClass())->foo();
MyClass 把 MyBaseClass 的 foo 方法替换了。

接下来后面这样的代码，其实也是 MyClass 的 foo2.
MyBaseClass::G()->foo2();

为什么不是 GetInstance ? 因为，太长。


class DNAutoLoad extends DNSingleton
自动加载函数的类
        public function init($path,$path_common='')
初始化
设定 mvc 的目录， 和共享目录
共享目录主要用于多网站配合
目录中有
model
后缀 CommonModel
service
后缀 CommonService
为什么名字这么长
因为经常用到这么长名字说明你错了
你可以在子网站的类里扩展这些共享类。
        public function run()
执行。

class DNRoute extends DNSingleton
核心的路由类。
除了默认的按文件目录走的路由类，还支持类似 nodejs 的方式

        public static function URL($url=null)
路由了，要找到相对的 URL 用这个静态函数
顺便，这也写成全局函数，方便在 view 里调用。
尽管我不太支持在 view 里写代码，但这里是为了方便起见
        public function _url($url=null)
静态函数 URL 的实现函数。

        public function init($path)
初始化，设定目录
        public function set404($callback)
设置 404 的回调函数
        public function run()
运行
这才开始
        public function defaltRouteHandle()
默认的路由方法，公开是为了回调
        public function addDefaultRoute($callback)
添加其他路由方式
        public function defaltDispathHandle()
		
默认的分发型路由，类似 nodejs 那种
        public function addDispathRoute($key,$callback)
添加 分发路由形式的路由
		
class DNConfig extends DNSingleton

        public static function Setting($key)
        public static function Get($key,$file_basename='config')
        public static function Load($file_basename)
        public function init($path,$path_common=null)
        public function getSetting($key)
        public function getConfig($key,$file_basename='config')
        public function loadConfig($file_basename='config')
		
class DNException extends Exception

        public static function ThrowOn($flag,$message,$code=0)
        public static function SetDefaultAllExceptionHandel($callback)
        public static function HandelAllException()
        public static function ManageException($ex)
        public static function SetErrorHandel($error_handel)
        public static function OnException($ex)
		
class DNDB extends DNSingleton
        public static function Show($view,$data=array(),$close_db=true)
        public static function return_json($ret)
        public static function return_redirect($url)
        public static function return_route_to($url)
        public function _show($view,$data=array(),$use_wrapper=true)
        public function init($path)
        public function setBeforeShow($callback)
        public function showBlock($view,$data)
        public function assign($key,$value)
        public function setWrapper($head_file,$foot_file)


class DNDB extends DNSingleton
        public function init($config)
        public function check_connect()
        public function getPDO()
        public function setPDO($pdo)
        public function close()
        public function quote($string)
        public function quote_array($array)
        public function fetchAll($sql)
        public function fetch($sql)
        public function fetchColumn($sql)
        public function exec($sql)
        public function rowCount()
        public function lastInsertId()
        public function get($table_name,$id,$key='id')
        public function insert($table_name,$data,$return_last_id=true)
        public function delete($table,$id,$key='id')
        public function update($table_name,$id,$data,$key='id')
		
class DNMVCS extends DNSingleton
        public static function Service($name)
        public static function Model($name)
        public function _load($name,$type)
        public function onShow404()
        public function onException($ex)
        public function onOtherException($ex)
        public function onDebugError($errno, $errstr, $errfile)
        public function onBeforeShow()
        public function init($path='',$path_common='')
        public function run()
        public function isDev()
        public function onErrorHandler($errno, $errstr, $errfile, $errline)
        public static function CallAPI($service,$method,$input)
class DNController
class DNService extends DNSingleton
class DNModel extends DNSingleton






## 还有什么要说的
