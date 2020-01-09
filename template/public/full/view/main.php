<?php declare(strict_types=1);
use MY\Base\Helper\ViewHelper as V;
use MY\Base\Helper\ControllerHelper as C;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <title>Hello DuckPHP!</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  
  <link rel="Stylesheet" type="text/css" href="/style.css">
  <style>
  pre {
    background-color: #ddd;
    border:1px gray solid;
  }
  </style>
</head>
<body>
<h1>欢迎来到 DuckPHP 的全功能演示页面</h1>
<div>
欢迎使用 DuckPHP ，这个 Demo 会尽力展示所有 DuckPHP 的可用操作。<br />
你通过 /full/public/index.php 访问本页面。<br />
说明你已经把 web 目录设置正确。 <br />

</div>

<fieldset>
<legend> nginx 配置 </legend>
<pre>
try_files $uri $uri/ /index.php$request_uri;
location ~ \.php {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_split_path_info ^(.*\.php)(.*)$;
    fastcgi_param PATH_INFO $fastcgi_path_info;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
</pre>
这是最保证没问题的配置。 有人有这种简化配置，但现实会告你，这样会导致子目录没法用。
<pre>
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
</pre>
使用 PHP 的内置服务器，就完全不用自己手动配置 PATH_INFO;

如果你没有 设置PATH_INFO，你可以切到 <a href="/full/public/no-path-info.php" target="_blank">“一个文件全部模式”</a>来查看</br>
为什么要这个 url 是  /full/public/index.php。而不是 /full/index.php 。<br >
因为这个全演示页面要做到直接把网站根目录调到 full/public/ 就是个典型的 DuckPHP 工程。 <br />
安全事项：<br />
full 工程 没设置 置为完全对外。

data 下的 database.sql ， bin 目录下面的 dumpsql.sh 都为可以直接访问。
真正设置时候你只需要放 public 在根目录就行了。

</fieldset>
<fieldset>
<legend> 文件结构 </legend>
template 工程的桩代码,完整的默认目录结构
<pre>
|-- app                             // psr-4 标准的自动加载目录
|   |-- Base                        // 基类放在这里
|   |   |-- App.php                 // 默认框架入口文件
|   |   |-- BaseController.php      // 控制器基类
|   |   |-- BaseModel.php           // 模型基类
|   |   |-- BaseService.php         // 服务基类
|   |   `-- Helper                  // 助手目录
|   |       |-- ControllerHelper.php// 控制器助手类
|   |       |-- ModelHelper.php     // 模型助手类
|   |       |-- ServiceHelper.php   // 服务助手类
|   |       `-- ViewHelper.php      // 视图助手类
|   |-- Controller                  // 控制器目录
|   |   `-- Main.php                // 默认控制器
|   |-- Model                       // 模型放在里
|   | `-- TestModel.php             // 测试模型
|   `-- Service                     // 服务目录
|     `-- TestService.php           // 测试服务
|-- config                          // 配置文件放这里
|   |-- config.php                  // 配置，目前是空数组
|   |-- setting.php                 // 设置，这个文件不要放在版本控制下
|   `-- setting.sample.php          // 设置模板，去除敏感信息的模板
|-- start_server.php                // 启动 Htttp 服务
|-- public                          // 网站目录
|   `-- index.php                   // 主页，入口页
`-- view                            // 视图文件放这里，可调
    |-- main.php                    // 视图文件
    `-- _sys                        // 系统错误视图文件放这里
        |-- error_404.php           // 404 页面
        |-- error_500.php           // 500 页面
        |-- error_debug.php         // 调试的时候显示的视图
        `-- error_exception.php     // 异常页面
</pre>
<pre>
Controller 目录下的是 文件方式的路由
假定网站域名是 127.0.0.1
访问
http://127.0.0.1/a/b/c 对应的文件是 app/Controller/a/b.php 对应方法是 MY\Controller\a\b->c();
如果只有一级
http://127.0.0.1/d/ 则对应的是 MY\Controller\Main->d() ，注意不是 MY\Controller\d->index();
命名空间，可以调。 路由表模式也有，都属于后面的高级功能
</pre>
</fieldset>
<fieldset>
<legend>理解架构图</legend>
<pre>
           /-> View-->ViewHelper as V
Controller --> Service ------------------------------ ---> Model
         \         \   \               \  /                  \
          \         \   \-> LibService ----> ExModel----------->ModelHelper as M
           \         \             \                
            \         ---------------->ServiceHelper as S
             \-->ControllerHelper as C
</pre>
</fieldset>
<fieldset>
<legend> 基础演示代码 </legend>
<a href="/test/done">点这里查看成品</a>
<h3>任务</h3>
<p>路径： http://127.0.0.1:8080/test/done</p>
<p>作用： 显示当前时间的任务。</p>

<p>对照目录结构我们要加个 test/done 显示当前时间
都在各代码段里注释了文件所在相对工程目录的位置
</p>

<h3>View 视图</h3>
先做出要显示的样子。
<pre>
&lt;?php // view/test/done.php ?>
&lt;!doctype html>&lt;html><body>
&lt;h1>test&lt;/h1>
&lt;div>&lt;?=$var ?>&lt;/div>
&lt;/body>&lt;/html>
</pre>
<h3>Controller控制器</h3>
写 /test/done 控制器对应的内容
<pre>
&lt;?php
// app/Controller/test.php
namespace MY\Controller;

// use MY\Base\BaseController;
use MY\Base\Helper\ControllerHelper as C;
use MY\Service\MiscService;

class test // extends BaseController
{
    public function done()
    {
        $data=[];
        $data['var']=C::H(MiscService::G()->foo());
        C::Show($data); // C::Show($data,'test/done');
    }
}
</pre>

<p>控制器里，我们处理外部数据，不做业务逻辑，业务逻辑在 Service 层做。</p>

<p>BaseController  这个基类，如果不强制要求也可以不用。</p>

<p>MY 这个命名空间前缀可在选项 ['namespace'] 中变更。</p>

<p>C::H 用来做 html编码。</p>

<p>C::Show($data); 是 C::Show($data,'test/done'); 的缩写， 调用 test/done 这个视图。</p>

<h3>Service 服务</h3>
业务逻辑层。
<pre>
&lt;?php
// app/Service/MiscService.php
namespace MY\Service;

use MY\Base\Helper\ServiceHelper as S;
use MY\Base\BaseService;
use MY\Model\MiscModel;

class MiscService extends BaseService
{
    public function foo()
    {
        $time=MiscModel::G()->getTime();
        $ret="&lt;".$time.">";
        return $ret;
    }
}
</pre>

<p>BaseService 也是不强求的，我们 extends BaseService 是为了能用 G 函数这个单例方法</p>

<p>这里调用了 MiscModel </p>

<h3>Model 模型</h3>

完成 MiscModel 。

Model 类是实现基本功能的。

<pre>
&lt;?php
// app/Model/MiscModel.php
namespace MY\Model;

use MY\Base\BaseModel;
use MY\Base\Helper\ModelHelper as M;

class MiscModel extends BaseModel
{
    public function getTime()
    {
        return DATE(DATE_ATOM);
    }
}
</pre>

<p>同样 BaseModel 也是不强求的，我们 extends BaseModel 是为了能用 G 函数这个单例方法</p>

<h3>最后显示结果</h3>
<pre>
test

&lt;2019-04-19T22:21:49+08:00>
</pre>
</fieldset>
<fieldset>
<legend> 助手类参考 </legend>
<p>助手类是什么</p>
<p>
@ProjectNamespace\Base\Helper\  命名空间下的类。 @ProjectNamespace 是你的命名空间。</p>
<p> 不同助手类在在不同地方使用。</p>
<p> 如  @ProjectNamespace\Base\Helper\ControllerHelper as C 用于 @ProjectNamespace\Controller\ 下面的所有类。</p>
<p> 其他助手类类推。</p>
<p>助手类只有静态方法，也许会有核心人员扩展的静态方法，你可以用  <a href="<?=C::URL('AllHelper/index#GetExtendStaticStaticMethodList')?>">GetExtendStaticStaticMethodList()</a> 来查看</p>

</p>
<dl>
<dt><a href="<?=C::URL('AllHelper/index')?>">全部助手类共有静态方法</a></dt>
<dd>（共计 11个），所有助手类都要用到</dd>
<dt><a href="<?=C::URL('ViewHelper/index')?>">ViewHelper</a></dt>
<dd>（共计 5 个），View 层的助手方法</dd>
<dt><a href="<?=C::URL('ServiceHelper/index')?>">ServiceHelper</a></dt>
<dd>（共计 3 个），Service层 的助手类做的配置工作</dd>
<dt><a href="<?=C::URL('ModelHelper/index')?>">ModelHelper</a></dt>
<dd>（共计 3 个），Model 层， 获得数据库的方法。不包含于 ControllerHelper</dd>
<dt><a href="<?=C::URL('ControllerHelper/index')?>">ControllerHelper</a></dt>
<dd>（共计 28个），Controller 层最复杂，助手类包含配置，显示相关，还包含跳转和其他其他复杂的使用</dd>
</dl>
<p>
当然，所有静态方法都在 App 类里实现。
特殊例外的是 SessionService 这个特殊类就引用了 App 类。
非核心代码请勿直接引用其他核心代码。
</p>
</fieldset>

<fieldset>
<legend>数据库学习</legend>
<h3>数据库配置</h3>
config/setting.php 里
<pre>
'database_list'=>[
    [
    'dsn'=>'mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8mb4;',
    'username'=>'admin',
    'password'=>'123456',
    'driver_options'=>[],
    ],
],
</pre>
需要注意的是 DuckPHP 是支持多个数据库的，所以是二维数组。

<pre>
数据库使用
use MY\Base\Helper\ModelHelper as M;
$sql="select 1+? as t";
$ret=M::DB()->fetchColumn($sql,100);
var_dump($ret);
var_dump(M::DB());

</pre>

<dl>
<dt>close()</dt>
<dd>关闭数据库</dd>
<dt>execute($sql, ...$args)</dt>
<dd>执行 sql 语句 </dd>
<dt>fetchAll($sql, ...$args)</dt>
<dd>取得多行 SQL 的执行结果 </dd>
<dt>fetch($sql, ...$args)</dt>
<dd>取得单 SQL 的执行结果 </dd>
<dt>fetchColumn($sql, ...$args)</dt>
<dd>取得单个 SQL 的执行结果 </dd>
<dt>quote($string)</dt>
<dd> SQL 编码</dd>
<dt>rowCount()</dt>
<dd>函数总计</dd>
<dt>lastInsertId()</dt>
<dd>取得插入ID</dd>
</dl>
高级方法
<pre>
    public function findData($table_name, $id, $key = 'id')
    public function insertData($table_name, $data, $return_last_id = true)
    public function deleteData($table_name, $id, $key = 'id', $key_delete = 'is_deleted')
    public function updateData($table_name, $id, $data, $key = 'id')
    public function quoteIn($array)
    public function quoteSetArray($array)
    public function qouteInsertArray($array)
</pre>
</fieldset>
<hr /><hr /><hr />
以上是普通开发者的 需要了解的知识。
<hr /><hr /><hr />
<fieldset>
<legend> App 类 其他助手方法。 </legend>
<pre>
    public static function assignPathNamespace($path, $namespace = null)
    public static function addRouteHook($hook, $position, $once = true) 
    public static function IsRunning()
    public static function IsInException()
    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    public static function On404(): void
    public static function OnException($ex)
    
    public static function &GLOBALS($k, $v = null)
    public static function &STATICS($k, $v = null, $_level = 1)
    public static function &CLASS_STATICS($class_name, $var_name)
</pre>

</fieldset>
<fieldset>
<legend> App 类 系统函数替代静态方法  </legend>
App类系统函数替代静态方法。是和系统函数同名，参数相同，用于在不同环境下兼容。
<pre>

    public static function header($output, bool $replace = true, int $http_response_code = 0)
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    public static function exit($code = 0)
    public static function set_exception_handler(callable $exception_handler)
    public static function register_shutdown_function(callable $callback, ...$args)
</pre>
</fieldset>
<fieldset>
<legend> App 类替代 Session 类  </legend>
和系统函数替代静态方法类似，也是和系统函数同名，参数相同。但由 SuperGlobal 类来实现。
<pre>
    public static function session_start(array $options = [])
    public static function session_id($session_id = null)
    public static function session_destroy()
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
</pre>
</legend>
<fieldset>
<legend> App 类 主流程方法  </legend>
<pre>
    public static function RunQuickly(array $options = [], callable $after_init = null): bool
    public function init(array $options, object $context = null)
    protected function onInit()
    protected function reloadFlags(): void
    protected function initExtentions(array $exts): void
    protected function onRun()
    public function clear(): void
    protected function addBeforeRunHandler(?callable $handler): void

</pre>
</fieldset>
<fieldset>
<legend> App 类 主流程方法  </legend>
<pre>
    public function addBeforeShowHandler($handler)
    public function extendComponents($method_map, $components = []): void
    public function cloneHelpers($new_namespace, $componentClassMap = [])
</pre>
</fieldset>
<fieldset>
<legend> 重写方法  </legend>
<pre>
重写方法很简单，在 MY\Base\App 里，重写就行， 重写的静态方法不会被助手类调用，你要重写的是相关的动态方法。
</pre>
</fieldset>
<fieldset>
<legend> 入口文件 public/index.php </legend>
<pre>
require_once(__DIR__.'/../vendor/autoload.php');        // @DUCKPHP_HEADFILE
$path=realpath(__DIR__.'/../');
$namespace= rtrim('MY\\', '\\');                    // @DUCKPHP_NAMESPACE
$options=[
    'path' => $path,
    'namespace' => $namespace,
    'error_404' => '_sys/error_404',
    'error_500' => '_sys/error_500',
    'error_exception' => '_sys/error_exception',
    'error_debug' => '_sys/error_debug',
];
\DuckPhp\App::RunQuickly($options, function () {
});
</pre>
这部分配合选项来学
</fieldset>


<fieldset>
<legend>样例</legend>
<div>
<a href="auth.php">这个是基本用户例子系统</a><br />
<a href="/full/public/u/index.php">这个是文章发布，管理，评论系统 </a>
其中，用户系统是拿 基本用户例子系统做为一个插件运行的！<br />
</div>
</fieldset>

<fieldset>
扩展<br />
第三方组件的使用和制作。
</fieldset>

<a href="<?=$url_phpinfo?>" target="_blank">点这里到 phpinfo 页面</a>来查看</br>

<a href="/u/index.php">“一个完整的文章系统”</a>
路由方式，子目录的路由



<hr/>
<hr/>
<hr />

<a href="<?=$url_phpinfo?>" target="_blank">phpinfo</a></br>
<fieldset>
    <legend>当前状态</legend>
<fieldset>
<legend>调用堆栈</legend>
<h3>实际调用堆栈</h3>
<pre>
<?php debug_print_backtrace(2);?>
</pre>
<h3>理论调用堆栈</h3>
<pre>
#0  include(@DOCUMENT_ROOT/full/view/main.php)   [@DuckPhp/Core/View.php:52]                        // 包含  View 文件
#1  DuckPhp\Core\View->_Show()                   [@DuckPhp/Core/App.php:751]                        // View 类实际处理 视图
#2  DuckPhp\Core\App->_Show()                    [@DuckPhp/Core/App.php:707]                        // App::Show 的内部实现，处理一些东西，转由 View 类出来
#3  DuckPhp\Core\App::Show()                     [@DuckPhp/Core/Helper/ControllerHelper.php:64]     // 未接管情况下，ControllerHelper 传递到实际的 App::Show
#4  DuckPhp\Core\Helper\ControllerHelper::Show() [@Project_namespace_path/Controller/Main.php:21]   // 调用 ControllerHelper::Show 显示页面
----
#5  MY\Controller\Main->index()                  [@DuckPhp/Core/Route.php:280]                      // index 方法
#6  DuckPhp\Core\Route->defaultRunRouteCallback()[@DuckPhp/Core/Route.php:211]                      // 默认路由方法
#7  DuckPhp\Core\Route->run()                    [@DuckPhp/Core/App.php:277]                        // 路由，处理钩子等。
#8  DuckPhp\Core\App->run()                      [@DuckPhp/Core/App.php:138]                        // App run 方法开始运行
#9  DuckPhp\Core\App::RunQuickly()               [@DOCUMENT_ROOT/full/public/index.php:15]          // 快速运行
</pre>
</fieldset>
<?php V::ShowBlock('inc-file');?>
<?php V::ShowBlock('inc-superglobal');?>
</fieldset>
<pre>
.........~+=+++=++......................
.......,++++++++++=+....................
......~++++++++++++++...................
......+++++M=++++++++...................
.=$$$$=+++NNN+++++++++..................
...Z$$Z=++++++++++++++..................
..$$$$$++++++++++++++..............++...
......,+++++++++++++~.............+++...
........+++++++++++~:,.........,+=++=...
..........++++++++++++=++==++++++++++...
........++++++++++++++++++++++++++++=...
......==++++++????????+=+++++++++=++=...
.....+++++++?????????????????????++++=..
....+++++++??????????????????????++++=..
....+++++++????????????????????++++++~..
....++++++=+??????????????????+++++++...
....~+++++++???????????????+++=+++++~...
.....+++++++++??????????+++++++++++=....
......+=++++++++++++=+++++++++++++~.....
.......:+++++++++++++++++++++++++.......
</pre>
</body>
</html>