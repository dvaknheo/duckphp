<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Foundation\Controller;

use DuckPhp\Component\Configer;
use DuckPhp\Component\DbManager;
use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\Pager;
use DuckPhp\Core\App;
use DuckPhp\Core\CoreHelper;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonExTrait;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\View;
use DuckPhp\GlobalAdmin\Admin;
use DuckPhp\GlobalUser\User;

class ControllerHelper
{
    use SingletonExTrait;

    const EVENT_ACTION_USER_REGISTERING = User::EVENT_ACTION_USER_REGISTERING;
    const EVENT_ACTION_USER_REGISTERED = User::EVENT_ACTION_USER_REGISTERED;
    const EVENT_ACTION_USER_LOGINING = User::EVENT_ACTION_USER_LOGINING;
    const EVENT_ACTION_USER_LOGINED = User::EVENT_ACTION_USER_LOGINED;
    const EVENT_ACTION_USER_LOGOUTING = User::EVENT_ACTION_USER_LOGOUTING;
    const EVENT_ACTION_USER_LOGOUTED = User::EVENT_ACTION_USER_LOGOUTED;

    const EXCEPTION_CODE_USER_NEED_LOGIN = User::EXCEPTION_CODE_USER_NEED_LOGIN;
    const EXCEPTION_MESSAGE_USER_NEED_LOGIN = User::EXCEPTION_MESSAGE_USER_NEED_LOGIN;
    const EXCEPTION_CODE_USER_NEED_PERMISSION = User::EXCEPTION_CODE_USER_NEED_PERMISSION;
    const EXCEPTION_MESSAGE_USER_NEED_PERMISSION = User::EXCEPTION_MESSAGE_USER_NEED_PERMISSION;

    const EVENT_ACTION_ADMIN_LOGINING = Admin::EVENT_ACTION_ADMIN_LOGINING;
    const EVENT_ACTION_ADMIN_LOGINED = Admin::EVENT_ACTION_ADMIN_LOGINED;
    const EVENT_ACTION_ADMIN_LOGOUTING = Admin::EVENT_ACTION_ADMIN_LOGOUTING;
    const EVENT_ACTION_ADMIN_LOGOUTED = Admin::EVENT_ACTION_ADMIN_LOGOUTED;

    const EXCEPTION_CODE_ADMIN_NEED_LOGIN = Admin::EXCEPTION_CODE_ADMIN_NEED_LOGIN;
    const EXCEPTION_MESSAGE_ADMIN_NEED_LOGIN = Admin::EXCEPTION_MESSAGE_ADMIN_NEED_LOGIN;
    const EXCEPTION_CODE_ADMIN_NEED_PERMISSION = Admin::EXCEPTION_CODE_ADMIN_NEED_PERMISSION;
    const EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION = Admin::EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION;

    public static function Setting($key = null, $default = null)
    {
        return App::Setting($key, $default);
    }
    public static function AppOptions(string $key, $default = null)
    {
        return App::_()->options[$key] ?? $default;
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
    public static function getRouteCallingClass(): ?string
    {
        return Route::_()->getRouteCallingClass();
    }
    public static function getRouteCallingMethod(): ?string
    {
        return Route::_()->getRouteCallingMethod();
    }
    public static function PathInfo(): ?string
    {
        return Route::PathInfo();
    }
    public static function Url($url = null)
    {
        return Route::_()->_Url($url);
    }
    public static function Domain(bool $use_scheme = false): string
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
        return App::_()->_Show($data, $view);
    }
    public static function checkInstall(?string $url_install = null)
    {
        App::_()->checkInstallToPage($url_install);
    }

    public static function setViewHeaderFooter($head_file = null, $foot_file = null)
    {
        return View::_()->setViewHeaderFooter($head_file, $foot_file);
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
    public static function ShowJson($ret, $flags = 0)
    {
        return CoreHelper::ShowJson($ret, $flags);
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
    /**
     * @param array<string, mixed> $classes
     */
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
    public static function ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return CoreHelper::_()->_ControllerThrowOn($flag, $message, $code, $exception_class);
    }

    /////////////
    public static function IsPost()
    {
        return SuperGlobal::_()->_SERVER('REQUEST_METHOD', 'GET') === 'POST';
    }
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
    public static function Pager($new = null)
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
    public static function FireGlobalEvent($event, ...$args)
    {
        return GlobalEvent::_()->fire($event, ...$args);
    }
    public static function OnGlobalEvent($event, $callback)
    {
        return GlobalEvent::_()->on($event, $callback);
    }
    //////////////////////
    /**
     * @return \DuckPhp\GlobalAdmin\AdminActionInterface
     */
    public static function Admin()
    {
        return Admin::_();
    }
    public static function AdminId(bool $check_login = true)
    {
        return Admin::_()->id($check_login);
    }
    public static function AdminName(bool $check_login = true)
    {
        return Admin::_()->name($check_login);
    }
    /**
     * @return \DuckPhp\GlobalAdmin\AdminServiceInterface
     */
    public static function AdminService()
    {
        return Admin::_()->service();
    }
    /**
     * @return \DuckPhp\GlobalUser\UserActionInterface
     */
    public static function User()
    {
        return User::_();
    }
    public static function UserId(bool $check_login = true)
    {
        return User::_()->id($check_login);
    }
    public static function UserName(bool $check_login = true)
    {
        return User::_()->name($check_login);
    }
    /**
     * @return \DuckPhp\GlobalUser\UserServiceInterface
     */
    public static function UserService()
    {
        return User::_()->service();
    }
}
