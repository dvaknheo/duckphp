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
    <dt><a href="#">IsDebug()</a></dt>
    <dd>
        IsDebug 方法，用于判断平台
    </dd>
    <dt><a href="#">IsRealDebug()</a></dt>
    <dd>
        IsRealDebug 切莫乱用。用于环境设置为其他。比如线上环境，但是还是要特殊调试的场合。
        如果没被修改，这将和  IsDebug() 一致。
    </dd>
    <dt><a href="#">Platform()</a></dt>
    <dd>获得当前所在平台,设置字段里的 duckphp_platform ，用于判断当前是哪台机器等</dd>
    
    <dt><a href="#">trace_dump()</a></dt>
    <dd>打印当前堆栈，类似 debug_print_backtrce(2)</dd>
    <dt><a href="#">var_dump(...$args)</a></dt>
    <dd> 替代 var_dump 函数，不是debug 模式下，不会现实，安全使用</dd>

    <dt><a href="#">GetExtendStaticStaticMethodList()</a></dt>
    <dd>获得当前助手类扩展了什么，这个常用于查看核心代码给助手类加了什么 </dd>
    
    <dt><a href="#">__callStatic</a></dt>
    <dd>静态方法已经被扩展, 会有额外代码，</dd>
    <dt><a href="#">ThrowOn($flag, $message, $code = 0, $exception_class = null)</a></dt>
    <dd>
        如果 flag 成立，那么抛出消息为 $message, code为 $code, $exception_class 的异常，如 $exception_class =null ，则默认为 Exception::class 的异常。 
        ThrowOn($flag, $message, $exception_class = null)
        简化版本， $code=0;
        </dd>
    <dt><a href="#">Logger()</a></dt>
    <dd> 获得 PSR 日志类</dd>
    <dt><a href="#">AssignExtendStaticMethod($key, $value = null)</a></dt>
    <dd>高级函数，一般不要使用</dd>
    <dt><a href="#">CallExtendStaticMethod($name, $arguments)</a></dt>
    <dd>高级函数，一般不要使用</dd>
    </dl>
<hr />
排序
    <dl>
    </dl>
</body>
</html>
