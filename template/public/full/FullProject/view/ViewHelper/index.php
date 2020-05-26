<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <title>Hello DuckPHP!</title>
</head>
<body>
本页面展示 ViewHelper 方法。
ViewHelper 是在View 里使用。

ViewHelper 默认的方法在 ControllerHelper 里都有。
但是 ViewHelper 不是作为  ControllerHelper 的子集。

<fieldset>
    <legend> ViewHelper</legend>
<dl>
    <dt><a href="#">H($str)</a></dt>
    <dd> HTML 编码</dd>
    <dt><a href="#">L($str,$args=[])</a></dt>
    <dd> 语言处理函数，后面的关联数组替换 '{$key}'</dd>
    <dt><a href="#">HL($str, $args=[])</a></dt>
    <dd> 对语言处理后进行 HTML 编码</dd>
    <dt><a href="#">URL($url)</a></dt>
    <dd> 获得相对 url 地址</dd>
    <dt><a href="#">ShowBlock($view, $data = null)</a></dt>
    <dd> 包含下一个 $view ， 如果 $data =null 则带入所有当前作用域的变量。 否则带入 $data 关联数组的内容 </dd>

</dl>
</fieldset>

</body>
</html>
