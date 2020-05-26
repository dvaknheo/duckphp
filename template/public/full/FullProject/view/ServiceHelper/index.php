<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <title>Hello DuckPHP!</title>
</head>
<body>

本页面展示 SerivceHelper 方法。
ServiceHelper 用于 Service 层。
只用到了
<fieldset>
    <legend>ServiceHelper</legend>
    <dl>
        <dt><a href="#">Setting($key)</a></dt>
        <dd>读取设置,设置默认在 config/setting.php 里， .env 的内容也会加进来</dd>
        <dt><a href="#">Config($key, $file_basename = 'config')</a></dt>
        <dd>读取配置，从 config/$file_basename.php 里读取配置</dd>
        <dt><a href="#">LoadConfig($file_basename)</a></dt>
        <dd>载入 config/$file_basename.hp 的配置段</dd>
    </dl>
</fieldset>

</body>
</html>
