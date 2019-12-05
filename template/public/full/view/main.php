<?php
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
欢迎使用 DuckPHP ，这个 Demo 会尽力展示所有 DuckPHP 的可用操作。<br />
<fieldset>
<legend> nginx 配置 </legend>
DuckPHP 的 nginx 配置很简单，就一句话。
本工程是在 子目录下配置的。
<pre>
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
</pre>
如果你没有 设置PATH_INFO，你可以切到 <a href="/full/public/no-path-info.php" target="_blank">“一个文件全部模式”</a>来查看</br>
为什么要这个 url 是  /full/public/index.php。而不是 /full/index.php 。<br >
因为这个全演示页面要做到直接把网站根目录调到 full/public/ 就是个典型的 DuckPHP 工程。 <br />
安全事项：<br />
本 Demo 没设置为
</fieldset>
<fieldset>
<legend> 文件结构 </legend>
工程的桩代码,完整的默认目录结构
<pre>
+---app                     // psr-4 标准的自动加载目录
|   +---Base                // 基类放在这里
|   |   |   App.php         // 默认框架入口文件
|   |   |   BaseController.php  // 控制器基类
|   |   |   BaseModel.php   // 模型基类
|   |   |   BaseService.php // 服务基类
|   |   \---Helper
|   |           ControllerHelper.php    // 控制器助手类
|   |           ModelHelper.php     // 模型助手类
|   |           ServiceHelper.php   // 服务助手类
|   |           ViewHelper.php      // 视图助手类
|   +---Controller          // 控制器目录
|   |       Main.php        // 默认控制器
|   +---Model               // 模型放在里
|   |       TestModel.php   // 测试模型
|   \---Service             // 服务目录
|           TestService.php // 测试 Service
+---config                  // 配置文件放这里
|       config.php          // 配置，目前是空数组
|       setting.sample.php  // 设置，去除敏感信息的模板
+---view                    // 视图文件放这里，可调
|   |   main.php            // 视图文件
|   \---_sys                // 系统错误视图文件放这里
|           error_404.php   // 404 页面
|           error_500.php   // 500 页面
|           error_debug.php // 调试的时候显示的视图
|           error_exception.php // 异常页面
+--public                   // 网站目录
|       index.php           // 主页，入口页
\       start_server.php    // 启动 Htttp 服务
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
</fieldset>
<fieldset>
<legend> 助手类参考 </legend>
<a href="<?=C::URL('AllHelper/index')?>">全部助手类共有方法</a><br />
<a href="<?=C::URL('ControllerHelper/index')?>">ControllerHelper</a><br />
<a href="<?=C::URL('ServiceHelper/index')?>">ServiceHelper</a><br />
<a href="<?=C::URL('ModelHelper/index')?>">ModelHelper</a><br />
<a href="<?=C::URL('ViewHelper/index')?>">ViewHelper</a><br />
</fieldset>

<a href="<?=$url_phpinfo?>" target="_blank">点这里到 phpinfo 页面</a>来查看</br>

<a href="/u/index.php">“一个完整的文章系统”</a>
路由方式，子目录的路由

Time Now is <?php echo $var;?>
<div><?=$html_pager?></div>
</div>

<fieldset>
<legend>ControllerHelper 类的可用方法</legend>
<dl>
    <dt>assignRewrite</dt>
    <dd>分配</dd>
</dl>
</fieldset>


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
</body>
</html>