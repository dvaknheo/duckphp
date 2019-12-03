<?php
use MY\Base\Helper\ViewHelper as V;
?>
<!doctype html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<body>
<h1>Hello DNMVCS</h1>
<div>
欢迎使用 DNMVCS.
<?php V::ShowBlock('inc-coroutine');?>

<a href="/OneFile.php">“一个文件全部模式”</a>
<a href="/u/index.php">“一个完整的文章系统”</a>
路由方式，子目录的路由

Time Now is <?php echo $var;?>
<div><?=$html_pager?></div>
</div>
<pre>
<?php
//$server=DNMVCS\SwooleHttpd\SwooleHttpd::Server();
//var_dump($server);
// co:sleep(3);?>

</pre>
<hr/>
<?php V::ShowBlock('inc-static.php');?>
<?php V::ShowBlock('inc-function.php');?>
<?php V::ShowBlock('inc-backtrace');?>
<?php V::ShowBlock('inc-file');?>

<?php V::ShowBlock('inc-superglobal');?>

<?php V::ShowBlock('inc-coroutine');?>

</body>
</html>