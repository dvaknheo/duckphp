<?php

//throw new Exception("test");
?><!doctype html><!-- ddddddddddddddddddddddddd -->
<html>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<body>
<?php if($user){ ?>
	欢迎你 <?=$user['username']?> 
	<a href="<?=$url_logout?>">登出</a>
<?php }else {?>
	<a href="<?=$url_login?>">登录</a>
	<a href="<?=$url_reg?>">注册</a>
<?php } ?>
[<a href="<?=$url_admin?>">管理</a>]
<h1></h1>
<fieldset>
<legend>最近文章</legend>
<ul>
<?php foreach($articles as $v){?>
	<li><a href="<?=$v['url']?>"><?=$v['title']?></a></li>
<?php }?>
</ul>
<?=$pager?>
<pre>
<?php 
$files=get_included_files();
sort($files);
var_export($files);?>
</pre>
<pre>
<?php debug_print_backtrace(2);?>
</pre>
</fieldset>
</body>
</html>