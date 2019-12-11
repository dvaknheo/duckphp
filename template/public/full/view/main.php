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
DuckPHP 的 nginx 配置很简单，就一句话。
本工程是在 子目录下配置的。
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
架构图。
</fieldset>
<fieldset>
<legend> 助手类参考 </legend>
<div>
架构图
</div>
</fieldset>
<fieldset>
<legend> 助手类参考 </legend>
再学这部分就合格了。<br />
我们先学共有静态方法。
<a href="<?=C::URL('AllHelper/index')?>">全部助手类共有方法</a><br />
我们从简单的到复杂的开始学习
<a href="<?=C::URL('ModelHelper/index')?>">ModelHelper</a> 
ModelHelper 只有获得数据库的方法 <br />
<a href="<?=C::URL('ViewHelper/index')?>">ViewHelper 记为 V, </a><br />
<a href="<?=C::URL('ServiceHelper/index')?>">ServiceHelper</a><br />

ControllerHelper 最复杂，默认包含配置方法， 内容处理方法等。 ServiceHelper
<a href="<?=C::URL('ControllerHelper/index')?>">ControllerHelper</a><br />

当然，所有静态方法都在 App 类里实现。

SessionService 这个特殊类就引用了 App 类。
其他小弟，要是直接用App 类那就罚他。
</fieldset>

<fieldset>
<legend>数据库学习</legend>
数据库配置。
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
<pre>
数据库使用
var_dump(M::DB()); // 默认是 DB 类。
方法

public function close();
关闭数据库
public function execute($sql, ...$args);
public function fetchAll($sql, ...$args);
public function fetch($sql, ...$args);
public function fetchColumn($sql, ...$args);
public function quote($string);
public function rowCount()
public function lastInsertId()

高级方法
    public function findData($table_name, $id, $key = 'id')
    public function insertData($table_name, $data, $return_last_id = true)
    public function deleteData($table_name, $id, $key = 'id', $key_delete = 'is_deleted')
    public function updateData($table_name, $id, $data, $key = 'id')
    public function quoteIn($array)
    public function quoteSetArray($array)
    public function qouteInsertArray($array)
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
<a href="full/index.php">这个是文章发布，管理，评论系统 </a>
其中，用户系统是拿 基本用户例子系统做为一个插件运行的！<br />
</div>
</fieldset>
<fieldset>
扩展
</fieldset>
<fieldset>
第三方组件的使用和制作。
</fieldset>

<a href="<?=$url_phpinfo?>" target="_blank">点这里到 phpinfo 页面</a>来查看</br>

<a href="/u/index.php">“一个完整的文章系统”</a>
路由方式，子目录的路由

Time Now is <?php echo $var;?>
<div><?=$html_pager?></div>
</div>

<hr/>
<hr/>
<hr />

<a href="<?=$url_phpinfo?>" target="_blank">phpinfo</a></br>
<fieldset>
    <legend>当前状态</legend>
<?php V::ShowBlock('inc-backtrace');?>
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