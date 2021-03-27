# DuckPhp\Helper\AppHelper
[toc]

## 简介
这个助手类是 App 类中系统相关部分。

App 助手类。 
助手类，全静态方法

## 公开方法
public static function IsDebug()
public static function Platform()
public static function trace_dump()
public static function var_dump(...$args)

public static function CallException($ex)

public static function IsRunning()
public static function isInException()

public static function assignPathNamespace($path, $namespace = null)
public static function addRouteHook($hook, $position, $once = true)
public static function setUrlHandler($callback)

## 系统替代 函数
这些函数，和系统函数同名，目的是兼容 swoole/workerman 等平台。

public static function set_exception_handler(callable $exception_handler)
public static function register_shutdown_function(callable $callback, ...$args)
public static function session_start(array $options = [])
public static function session_id($session_id = null)
public static function session_destroy()
public static function session_set_save_handler(\SessionHandlerInterface $handler)


## 详解



public static function CallException($ex)
public static function IsRunning()
public static function InException()
public static function assignPathNamespace($path, $namespace = null)
public static function addRouteHook($hook, $position, $once = true)
public static function setUrlHandler($callback)
public static function set_exception_handler(callable $exception_handler)
public static function register_shutdown_function(callable $callback, ...$args)
public static function session_start(array $options = [])
public static function session_id($session_id = null)
public static function session_destroy()
public static function session_set_save_handler(\SessionHandlerInterface $handler)
public static function &GLOBALS($k, $v = null)
public static function &STATICS($k, $v = null, $_level = 1)
public static function &CLASS_STATICS($class_name, $var_name)
