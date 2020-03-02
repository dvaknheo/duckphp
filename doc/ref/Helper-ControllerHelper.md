# Helper\ControllerHelper

## 简介

控制器助手类
## 选项

## 公开方法
public static function Setting($key)
public static function Config($key, $file_basename='config')
public static function LoadConfig($file_basename)
public static function H($str)
public static function URL($url=null)
public static function Parameters()
public function getRouteCallingMethod()
public function setRouteCallingMethod($method)
public static function Show($data=[], $view=null)
public static function ShowBlock($view, $data=null)
public function setViewWrapper($head_file=null, $foot_file=null)
public function assignViewData($key, $value=null)
public static function ExitRedirect($url, $exit=true)
public static function ExitRedirectOutside($url, $exit=true)
public static function ExitRouteTo($url, $exit)
public static function Exit404($exit=true)
public static function ExitJson($ret, $exit=true)
public static function header($output, bool $replace = true, int $http_response_code=0)
public static function exit_system($code=0)
public function assignExceptionHandler($classes, $callback=null)
public function setMultiExceptionHandler(array $classes, $callback)
public function setDefaultExceptionHandler($callback)
public static function SG()
public static function &GLOBALS($k, $v=null)
public static function &STATICS($k, $v=null)
public static function &CLASS_STATICS($class_name, $var_name)
public static function session_start(array $options=[])
public function session_id($session_id=null)
public static function session_destroy()
public static function session_set_save_handler(\SessionHandlerInterface $handler)

## 详解

