<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Component\Configer;
use DuckPhp\Component\DbManager;
use DuckPhp\Component\Pager;
use DuckPhp\Core\App;
use DuckPhp\Core\CoreHelper;
use DuckPhp\Core\EventManager;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonTrait;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\View;
use DuckPhp\GlobalAdmin\GlobalAdmin;
use DuckPhp\GlobalUser\GlobalUser;

trait ControllerHelperTrait
{
    use SingletonTrait;
    public static function Setting($key = null, $default = null)
    {
        return App::Setting($key, $default);
    }
    public static function XpCall($callback, ...$args)
    {
        return CoreHelper::_()->_XpCall($callback, ...$args);
    }
    public static function Config($file_basename, $key = null, $default = null)
    {
        return Configer::_()->_Config($file_basename, $key, $default);
    }
    ///////////
    public static function getRouteCallingClass()
    {
        return Route::_()->getRouteCallingClass();
    }
    public static function getRouteCallingMethod()
    {
        return Route::_()->getRouteCallingMethod();
    }
    public static function PathInfo()
    {
        return Route::PathInfo();
    }
    public static function Url($url = null)
    {
        return Route::_()->_Url($url);
    }
    public static function Domain($use_scheme = false)
    {
        return Route::_()->_Domain($use_scheme);
    }
    public static function Res($url = null)
    {
        return Route::_()->_Res($url);
    }
    public static function Parameter($key = null, $default = null)
    {
        return Route::Parameter($key, $default);
    }
    ///////////////
    public static function Render($view, $data = null)
    {
        return View::_()->_Render($view, $data);
    }
    public static function Show($data = [], $view = '')
    {
        return View::_()->_Show($data, $view);
    }

    public static function setViewHeadFoot($head_file = null, $foot_file = null)
    {
        return View::_()->setViewHeadFoot($head_file, $foot_file);
    }
    public static function assignViewData($key, $value = null)
    {
        return View::_()->assignViewData($key, $value);
    }
    ////////////////////
    public static function IsAjax()
    {
        return CoreHelper::IsAjax();
    }
    public static function Show302($url)
    {
        return CoreHelper::Show302($url);
    }
    public static function Show404()
    {
        return CoreHelper::Show404();
    }
    public static function ShowJson($ret)
    {
        return CoreHelper::ShowJson($ret);
    }

    /////////////////
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    {
        return SystemWrapper::_()->_header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return SystemWrapper::_()->_setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit($code = 0)
    {
        return SystemWrapper::_()->_exit($code);
    }
    //exception manager
    public static function assignExceptionHandler($classes, $callback = null)
    {
        return ExceptionManager::_()->assignExceptionHandler($classes, $callback);
    }
    public static function setMultiExceptionHandler(array $classes, $callback)
    {
        return ExceptionManager::_()->setMultiExceptionHandler($classes, $callback);
    }
    public static function setDefaultExceptionHandler($callback)
    {
        return ExceptionManager::_()->setDefaultExceptionHandler($callback);
    }
    public static function ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return CoreHelper::_()->_ControllerThrowOn($flag, $message, $code, $exception_class);
    }
    /////////////
    public static function GET($key = null, $default = null)
    {
        return SuperGlobal::_()->_GET($key, $default);
    }
    public static function POST($key = null, $default = null)
    {
        return SuperGlobal::_()->_POST($key, $default);
    }
    public static function REQUEST($key = null, $default = null)
    {
        return SuperGlobal::_()->_REQUEST($key, $default);
    }
    public static function COOKIE($key = null, $default = null)
    {
        return SuperGlobal::_()->_COOKIE($key, $default);
    }
    public static function SERVER($key = null, $default = null)
    {
        return SuperGlobal::_()->_SERVER($key, $default);
    }
    /////////////
    public static function Pager($new)
    {
        return Pager::_($new);
    }
    public static function PageNo($new_value = null)
    {
        return Pager::PageNo($new_value);
    }
    public static function PageWindow($new_value = null)
    {
        return Pager::PageWindow($new_value);
    }
    public static function PageHtml($total, $options = [])
    {
        return Pager::PageHtml($total, $options);
    }
    ////
    public static function FireEvent($event, ...$args)
    {
        return EventManager::FireEvent($event, ...$args);
    }
    public static function OnEvent($event, $callback)
    {
        return EventManager::OnEvent($event, $callback);
    }
    //////////////////////
    public static function Admin()
    {
        return GlobalAdmin::_();
    }
    public static function AdminId($check_login = true)
    {
        return GlobalAdmin::_()->id($check_login = true);
    }
    public static function AdminName($check_login = true)
    {
        return GlobalAdmin::_()->name($check_login = true);
    }
    public static function AdminService()
    {
        return GlobalAdmin::_()->service();
    }
    public static function User()
    {
        return GlobalUser::_();
    }
    public static function UserId($check_login = true)
    {
        return GlobalUser::_()->id($check_login);
    }
    public static function UserName($check_login = true)
    {
        return GlobalUser::_()->name($check_login);
    }
    public static function UserService()
    {
        return GlobalUser::_()->service();
    }
}
