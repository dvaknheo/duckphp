<?php
namespace DNMVCS\Core\Helper;

use DNMVCS\Core\ExtendableStaticCallTrait;
use DNMVCS\Core\ThrowOn;
use DNMVCS\Core\App;

class ControllerHelper
{
    use ExtendableStaticCallTrait;
    use ThrowOn;
    
    // use ExtendableStaticCallTrait GetExtendStaticStaticMethodList to show static functions.
    
    public static function Setting($key)
    {
        return App::Setting($key);
    }
    public static function Config($key, $file_basename='config')
    {
        return App::Config($key, $file_basename);
    }
    public static function LoadConfig($file_basename)
    {
        return App::LoadConfig($file_basename);
    }
    
    public static function IsDebug()
    {
        return App::IsDebug();
    }
    public static function Platform()
    {
        return App::Platform();
    }
    ////////////////
    public static function URL($url=null)
    {
        return App::URL($url);
    }
    public static function Parameters()
    {
        return App::Parameters();
    }
    public function getRouteCallingMethod()
    {
        return App::getRouteCallingMethod();
    }
    public function setRouteCallingMethod($method)
    {
        return App::getRouteCallingMethod($method);
    }
    ///////////////
    public static function Show($data=[], $view=null)
    {
        return App::Show($data, $view);
    }
    public static function ShowBlock($view, $data=null)
    {
        return App::ShowBlock($view, $data);
    }
    public function setViewWrapper($head_file=null, $foot_file=null)
    {
        return App::setViewWrapper($head_file, $foot_file);
    }
    public function assignViewData($key, $value=null)
    {
        return App::assignViewData($key, $value);
    }
    ////////////////////
    public static function ExitRedirect($url, $only_in_site=true)
    {
        return App::ExitRedirect($url, $only_in_site);
    }
    public static function ExitRouteTo($url)
    {
        return App::ExitRedirect(static::URL($url), true);
    }
    public static function Exit404()
    {
        return App::Exit404();
    }
    public static function ExitJson($ret)
    {
        return App::ExitJson($ret);
    }
    /////////////////
    public static function header($output, bool $replace = true, int $http_response_code=0)
    {
        return App::header($output, $replace, $http_response_code);
    }
    public static function exit_system($code=0)
    {
        return App::exit_system($code);
    }
    //exception manager
    public function assignExceptionHandler($classes, $callback=null)
    {
        return App::assignExceptionHandler($classes, $callback);
    }
    public function setMultiExceptionHandler(array $classes, $callback)
    {
        return App::setMultiExceptionHandler($classes, $callback);
    }
    public function setDefaultExceptionHandler($callback)
    {
        return App::setDefaultExceptionHandler($callback);
    }
    //super global
    public static function SG()
    {
        return App::SG();
    }
    public static function &GLOBALS($k, $v=null)
    {
        return App::GLOBALS($k, $v);
    }
    public static function &STATICS($k, $v=null)
    {
        return App::STATICS($k, $v, 2); //Remark ,++;
    }
    public static function &CLASS_STATICS($class_name, $var_name)
    {
        return App::CLASS_STATICS($class_name, $var_name);
    }
    // super global  session
    public static function session_start(array $options=[])
    {
        return App::session_start($options);
    }
    public function session_id($session_id=null)
    {
        return App::session_id($session_id);
    }
    public static function session_destroy()
    {
        return App::session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return App::session_set_save_handler($handler);
    }
}
