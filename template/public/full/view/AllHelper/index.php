<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <title>Hello DuckPHP!</title>
</head>
<body>

本页面展示 所有助手类共有的方法。
<fieldset>
    <legend>ControllerHelper 方法</legend>
    <dl>
    <dt><a href="#">IsDebug</a></dt>
    <dd>
        IsDebug 方法，用于判断平台
    </dd>
    <dt><a href="#">IsRealDebug</a></dt>
    <dd>
        IsRealDebug 切莫乱用。用于环境设置为其他。比如线上环境，但是还是要特殊调试的场合。
    </dd>
    <dt><a href="#">Platform</a></dt>
    <dd>获得当前所在平台</dd>
    <dt><a href="#">DumpTrace</a></dt>
    <dd>打印当前堆栈，类似 debug_print_backtrce(2)</dd>
    <dt><a href="#">var_dump</a></dt>
    <dd> 替代 var_dump 函数，不是debug 模式下，不会现实，安全使用</dd>
    <dt><a href="#">AssignExtendStaticMethod</a></dt>
    <dd>高级函数，一般不要使用</dd>
    <dt><a href="#">GetExtendStaticStaticMethodList</a></dt>
    <dd>获得当前方法扩展了什么 </dd>
    <dt><a href="#">CallExtendStaticMethod</a></dt>
    <dd>高级函数，一般不要使用</dd>
    <dt><a href="#">__callStatic</a></dt>
    <dd>静态方法已经被扩展</dd>
    <dt><a href="#">ThrowOn</a></dt>
    <dd>介绍</dd>
    <dt><a href="#">Logger</a></dt>
    <dd> 获得 PSR 日志类</dd>
    </dl>
<hr />
排序
    <dl>
    </dl>
</body>
</html>
