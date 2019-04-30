<?php
namespace DNMVCS\Core\Base;

use DNMVCS\Core\SingletonEx;
use DNMVCS\Core\App;
use DNMVCS\Core\ExceptionManager;
use DNMVCS\Core\View;
use DNMVCS\Core\Route;

use DNMVCS\Glue\GlueThrowOn;
use DNMVCS\Glue\GlueConfiger;

class ControllerHelper
{
    use SingletonEx;
    use GlueThrowOn;
    use GlueConfiger;

    public static function IsDebug()
    {
        return App::G()->is_debug;
    }
    public static function Platform()
    {
        return App::G()->platform;
    }

    
    ////////////////
    public static function URL($url=null)
    {
        return Route::G()->_URL($url);
    }
    public static function Parameters()
    {
        return Route::G()->_Parameters();
    }
    public function getRouteCallingMethod()
    {
        return Route::G()->getRouteCallingMethod();
    }
    
    ///////////////
    public static function Show($data=[], $view=null)
    {
        return App::G()->_Show($data, $view);
    }
    public static function ShowBlock($view, $data=null)
    {
        return View::G()->_ShowBlock($view, $data);
    }
    public function setViewWrapper($head_file=null, $foot_file=null)
    {
        return View::G()->setViewWrapper($head_file, $foot_file);
    }
    public function assignViewData($key, $value=null)
    {
        return View::G()->assignViewData($key, $value);
    }
    ////////////////////
    public static function ExitRedirect($url, $only_in_site=true)
    {
        return App::G()->_ExitRedirect($url, $only_in_site);
    }
    public static function ExitRouteTo($url)
    {
        return App::G()->_ExitRedirect(static::URL($url), true);
    }
    public static function Exit404()
    {
        App::On404();
        App::exit_system();
    }
    public static function ExitJson($ret)
    {
        return App::G()->_ExitJson($ret);
    }
    /////////////////
    public static function isInException()
    {
        return App::G()->is_in_exception;
    }

    public static function header($output, bool $replace = true, int $http_response_code=0)
    {
        return App::G()->_header($output, $replace, $http_response_code);
    }
    public static function exit_system($code=0)
    {
        return App::G()->_exit_system($code);
    }
    
    //exception manager
    public function assignExceptionHandler($classes, $callback=null)
    {
        return ExceptionManager::G()->assignExceptionHandler($classes, $callback);
    }
    public function setMultiExceptionHandler(array $classes, $callback)
    {
        return ExceptionManager::G()->setMultiExceptionHandler($classes, $callback);
    }
    public function setDefaultExceptionHandler($callback)
    {
        return ExceptionManager::G()->setDefaultExceptionHandler($callback);
    }
}
