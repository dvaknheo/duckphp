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
如果你没有 设置PATH_INFO，你可以切到 <a href="/no-path-info.php" target="_blank">“一个文件全部模式”</a>来查看</br>
<div>
Q: 为什么要这个 url 是  /full/public/index.php。而不是 /full/index.php 。<br >
A: 因为这个全演示页面要做到直接把网站根目录调到  /full/public/ 就是典型的 DuckPHP 工程。 <br />
</div>
<fieldset>
<legend> 文件结构 </legend>


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
<?php V::ShowBlock('inc-function.php');?>
<?php V::ShowBlock('inc-backtrace');?>
<?php V::ShowBlock('inc-file');?>
<?php V::ShowBlock('inc-superglobal');?>
</fieldset>
</body>
</html>