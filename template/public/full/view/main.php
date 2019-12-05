<?php
use MY\Base\Helper\ViewHelper as V;
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
如果你没有 设置PATH_INFO，你可以切到 <a href="/no-path-info.php" target="_blank">“一个文件全部模式”</a>来查看</br>
这个 Demo 不会引用第三方文件。 <br />
<div>
Q: 为什么要这个 url 是  /full/public/index.php。而不是 /full/index.php 。<br >
A: 因为这个全演示页面要做到直接把网站根目录调到  /full/public/ 就是典型的 DuckPHP 工程。 <br />
</div>
<div>

<a href="<?=$url_phpinfo?>" target="_blank">点这里到 phpinfo 页面</a>来查看</br>


<a href="<?=$url_phpinfo?>" target="_blank">点这里到 phpinfo 页面</a>来查看</br>
<a href="<?=$url_controller_helper?>" target="_blank">点这里查看助手类 C 有什么方法</a></br>
<a href="<?=$url_model_helper?>" target="_blank">点这里查看助手类 M 有什么方法</a></br>
<a href="<?=$url_service_helper?>" target="_blank">点这里查看助手类 S 有什么方法</a></br>
<a href="<?=$url_view_helper?>" target="_blank">点这里查看助手类 V 有什么方法</a></br>

<a href="/u/index.php">“一个完整的文章系统”</a>
路由方式，子目录的路由

Time Now is <?php echo $var;?>
<div><?=$html_pager?></div>
</div>
<?php V::ShowBlock('inc-static.php');?>

<hr/>
<hr/>
<hr />

<fieldset>
    <legend>当前状态</legend>
<?php V::ShowBlock('inc-function.php');?>
<?php V::ShowBlock('inc-backtrace');?>
<?php V::ShowBlock('inc-file');?>
<?php V::ShowBlock('inc-superglobal');?>
</fieldset>
</body>
</html>