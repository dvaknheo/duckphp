<?php declare(strict_types=1);
// var_dump(get_defined_vars());var_dump($this);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Hello DuckPhp!</title>
</head>
<body>
<h1>Hello DuckPhp</h1>
Now is [<?=$var?>]
<hr/>
<div>
    欢迎使用 DuckPhp ,<?php echo $var;?>
    <a href="<?=__url('test/done')?>">查看 Demo 结果</a>
</div>
<hr />
<a href="/full/doc.php"> DuckPhp 文档</a>
<hr />
<div>
常用例子，不需要单独配置
<ul>
    <li><a href="/files"> /files 查看示例堆栈和包含文件</a>
    <li><a href="/demo.php"> demo.php 单一文件演示所有操作</a>
    <li><a href="/helloworld.php"> helloworld.php 常见的 helloworld</a>
    <li><a href="/just-route.php">just-route.php 只要路由</a>
    <li><a href="/api.php/test.index">作为 api 服务器的例子，不需要控制器了 </a>
    <li><a href="/traditional.php">traditional.php 传统模式,一个文件解决，不折腾那么多 </a>
    <li><a href="/full/rpc.php">一个远程调用 json rpc 的例子(nginx 限定) </a>
</ul>
需要配置的其他例子，在 full 目录下
<ul>
    <li><a href="/full/auth.php">auth.php 简单的用户验证系统</a>
    <li><a href="/full/blog.php">blog.php 简单的博客</a>
    <li><a href="/full/dbtest.php">dbtest.php 数据库演示</a>
    <li><a href="/full/cover_test.php">cover_test.php 覆盖率测试</a>
</ul>
</div>
<pre>
<?php // var_dump(\DuckPhp\Core\App::G());?>
</pre>
</body>
</html>