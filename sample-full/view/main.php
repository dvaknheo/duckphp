<!doctype html>
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
<h1>完全性测试.</h1>
<fieldset>
<legend>基础测试</legend>
<div></div>
<h1>Hello DNMVCS</h1>
<div>
Time Now is <?php echo $var;?>
</div>
</fieldset>
<fieldset>
<legend>路由测试</legend>
<div>

</div>
</fieldset>
<fieldset>
<legend>增删改查</legend>
</fieldset>
<fieldset>
<legend>这里测试 DNMVCSEx</legend>
<div>
	<a href='#'>api 模式测试</a>
</div>
</fieldset>
</body>
</html>