<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <title>Hello DuckPHP!</title>
</head>
<body>

本页面展示 ModelHelper 方法。<br>
ModelHelper 用于 Model 层。 <br>
ModelHelper 只有数据库的三个独特方法。<br>
这几个方法在 ControllerHelper 里没有。<br>
这几个方法不是 DuckPhp\Core\App 里的。<br>
而是由 DuckPhp\App 加载 DuckPhp\Ext\DBManager 后添加的。<br>
如何使用 DB 对象，看数据库部分的介绍。<br>
<fieldset>
    <legend> ModelHelper</legend>
<dl>
    <dt><a href="#">DB($tag)</a></dt>
    <dd>获得 DB 数据库对象 ,第 $tag 个配置的数据库对象</dd>
    <dt><a href="#">DB_W()</a></dt>
    <dd>获得用于写入的 DB 对象,这是获得第 0 个配置列表里的数据库</dd>
    <dt><a href="#">DB_R()</a></dt>
    <dd>获得用于读取的 DB 对象，这是获得第 1 个配置列表里的数据库</dd>
</dl>
</fieldset>

</body>
</html>
