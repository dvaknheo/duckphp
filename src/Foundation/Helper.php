<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Foundation;

use DuckPhp\Core\SingletonExTrait;

/**
 * Four-layer union facade.
 *
 * Every method forwards to the Helper class of its own layer; the 12 names that
 * exist in more than one layer are forwarded according to the historical
 * conflict resolution (Business wins Setting/AppOptions/Config/XpCall/
 * FireGlobalEvent/OnGlobalEvent, System wins ThrowOn, Controller wins
 * header/setcookie/exit and AdminService/UserService).
 */
class Helper
{
    use SingletonExTrait;

    ////////// Model layer (Foundation\Model\Helper) //////////
    public static function Db($tag = null)
    {
        return \DuckPhp\Foundation\Model\Helper::Db($tag);
    }
    public static function DbForRead()
    {
        return \DuckPhp\Foundation\Model\Helper::DbForRead();
    }
    public static function DbForWrite()
    {
        return \DuckPhp\Foundation\Model\Helper::DbForWrite();
    }
    public static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string
    {
        return \DuckPhp\Foundation\Model\Helper::SqlForPager($sql, $pageNo, $pageSize);
    }
    public static function SqlForCountSimply(string $sql): string
    {
        return \DuckPhp\Foundation\Model\Helper::SqlForCountSimply($sql);
    }
    public static function DatabaseDriver(): string
    {
        return \DuckPhp\Foundation\Model\Helper::DatabaseDriver();
    }

    ////////// Business layer (Foundation\Business\Helper) //////////
    public static $EVENT_REGISTERING = 'registering';
    public static $EVENT_REGISTERED = 'registered';
    public static $EVENT_LOGINING = 'logining';
    public static $EVENT_LOGINED = 'logined';
    public static function Setting($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Business\Helper::Setting($key, $default);
    }
    public static function AppOptions(string $key, $default = null)
    {
        return \DuckPhp\Foundation\Business\Helper::AppOptions($key, $default);
    }
    public static function Config($file_basename, $key = null, $default = null)
    {
        return \DuckPhp\Foundation\Business\Helper::Config($file_basename, $key, $default);
    }
    public static function XpCall($callback, ...$args)
    {
        return \DuckPhp\Foundation\Business\Helper::XpCall($callback, ...$args);
    }
    public static function BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return \DuckPhp\Foundation\Business\Helper::BusinessThrowOn($flag, $message, $code, $exception_class);
    }
    public static function Cache($object = null)
    {
        return \DuckPhp\Foundation\Business\Helper::Cache($object);
    }
    public static function PathOfProject(): string
    {
        return \DuckPhp\Foundation\Business\Helper::PathOfProject();
    }
    public static function PathOfRuntime(): string
    {
        return \DuckPhp\Foundation\Business\Helper::PathOfRuntime();
    }
    public static function FireGlobalEvent($event, ...$args)
    {
        return \DuckPhp\Foundation\Business\Helper::FireGlobalEvent($event, ...$args);
    }
    public static function OnGlobalEvent($event, $callback)
    {
        return \DuckPhp\Foundation\Business\Helper::OnGlobalEvent($event, $callback);
    }
    public static function Validator($new = null)
    {
        return \DuckPhp\Foundation\Business\Helper::Validator($new);
    }
    public static function ValidatorFilter($data, $rules, $messages = [])
    {
        return \DuckPhp\Foundation\Business\Helper::ValidatorFilter($data, $rules, $messages);
    }
    public static function ValidatorCheck($data, $rules, $messages = [])
    {
        return \DuckPhp\Foundation\Business\Helper::ValidatorCheck($data, $rules, $messages);
    }
    public static function ValidatorValid($data, $rules, $messages = [])
    {
        return \DuckPhp\Foundation\Business\Helper::ValidatorValid($data, $rules, $messages);
    }

    ////////// Controller layer (Foundation\Controller\Helper) //////////
    public static $EVENT_ACTION_REGISTERING = 'action_registering';
    public static $EVENT_ACTION_REGISTERED = 'action_registered';
    public static $EVENT_ACTION_LOGINING = 'action_logining';
    public static $EVENT_ACTION_LOGINED = 'action_logined';
    public static $EVENT_ACTION_LOGOUTING = 'action_logouting';
    public static $EVENT_ACTION_LOGOUTED = 'action_logouted';
    public static function getRouteCallingClass(): ?string
    {
        return \DuckPhp\Foundation\Controller\Helper::getRouteCallingClass();
    }
    public static function getRouteCallingMethod(): ?string
    {
        return \DuckPhp\Foundation\Controller\Helper::getRouteCallingMethod();
    }
    public static function PathInfo(): ?string
    {
        return \DuckPhp\Foundation\Controller\Helper::PathInfo();
    }
    public static function Url($url = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::Url($url);
    }
    public static function Domain(bool $use_scheme = false): string
    {
        return \DuckPhp\Foundation\Controller\Helper::Domain($use_scheme);
    }
    public static function Res($url = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::Res($url);
    }
    public static function Parameter($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::Parameter($key, $default);
    }
    public static function Render($view, $data = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::Render($view, $data);
    }
    public static function Show($data = [], $view = '')
    {
        return \DuckPhp\Foundation\Controller\Helper::Show($data, $view);
    }
    public static function checkInstall(?string $url_install = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::checkInstall($url_install);
    }
    public static function setViewHeadFoot($head_file = null, $foot_file = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::setViewHeadFoot($head_file, $foot_file);
    }
    public static function assignViewData($key, $value = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::assignViewData($key, $value);
    }
    public static function IsAjax()
    {
        return \DuckPhp\Foundation\Controller\Helper::IsAjax();
    }
    public static function Show302($url)
    {
        return \DuckPhp\Foundation\Controller\Helper::Show302($url);
    }
    public static function Show404()
    {
        return \DuckPhp\Foundation\Controller\Helper::Show404();
    }
    public static function ShowJson($ret, $flags = 0)
    {
        return \DuckPhp\Foundation\Controller\Helper::ShowJson($ret, $flags);
    }
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    {
        return \DuckPhp\Foundation\Controller\Helper::header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return \DuckPhp\Foundation\Controller\Helper::setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit($code = 0)
    {
        return \DuckPhp\Foundation\Controller\Helper::exit($code);
    }
    public static function assignExceptionHandler($classes, $callback = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::assignExceptionHandler($classes, $callback);
    }
    public static function setMultiExceptionHandler(array $classes, $callback)
    {
        return \DuckPhp\Foundation\Controller\Helper::setMultiExceptionHandler($classes, $callback);
    }
    public static function setDefaultExceptionHandler($callback)
    {
        return \DuckPhp\Foundation\Controller\Helper::setDefaultExceptionHandler($callback);
    }
    public static function ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::ControllerThrowOn($flag, $message, $code, $exception_class);
    }
    public static function IsPost()
    {
        return \DuckPhp\Foundation\Controller\Helper::IsPost();
    }
    public static function GET($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::GET($key, $default);
    }
    public static function POST($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::POST($key, $default);
    }
    public static function REQUEST($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::REQUEST($key, $default);
    }
    public static function COOKIE($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::COOKIE($key, $default);
    }
    public static function SERVER($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::SERVER($key, $default);
    }
    public static function Pager($new = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::Pager($new);
    }
    public static function PageNo($new_value = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::PageNo($new_value);
    }
    public static function PageWindow($new_value = null)
    {
        return \DuckPhp\Foundation\Controller\Helper::PageWindow($new_value);
    }
    public static function PageHtml($total, $options = [])
    {
        return \DuckPhp\Foundation\Controller\Helper::PageHtml($total, $options);
    }
    public static function Admin()
    {
        return \DuckPhp\Foundation\Controller\Helper::Admin();
    }
    public static function AdminId(bool $check_login = true)
    {
        return \DuckPhp\Foundation\Controller\Helper::AdminId($check_login);
    }
    public static function AdminName(bool $check_login = true)
    {
        return \DuckPhp\Foundation\Controller\Helper::AdminName($check_login);
    }
    public static function AdminService()
    {
        return \DuckPhp\Foundation\Controller\Helper::AdminService();
    }
    public static function User()
    {
        return \DuckPhp\Foundation\Controller\Helper::User();
    }
    public static function UserId(bool $check_login = true)
    {
        return \DuckPhp\Foundation\Controller\Helper::UserId($check_login);
    }
    public static function UserName(bool $check_login = true)
    {
        return \DuckPhp\Foundation\Controller\Helper::UserName($check_login);
    }
    public static function UserService()
    {
        return \DuckPhp\Foundation\Controller\Helper::UserService();
    }

    ////////// System layer (Foundation\System\Helper) //////////
    public static function CallException(\Throwable $ex)
    {
        return \DuckPhp\Foundation\System\Helper::CallException($ex);
    }
    public static function RemoveEvent($event, $callback = null)
    {
        return \DuckPhp\Foundation\System\Helper::RemoveEvent($event, $callback);
    }
    public static function isRunning(): bool
    {
        return \DuckPhp\Foundation\System\Helper::isRunning();
    }
    public static function isInException(): bool
    {
        return \DuckPhp\Foundation\System\Helper::isInException();
    }
    public static function addRouteHook($callback, $position = 'append-outter', $once = true)
    {
        return \DuckPhp\Foundation\System\Helper::addRouteHook($callback, $position, $once);
    }
    public static function replaceController(string $old_class, string $new_class)
    {
        return \DuckPhp\Foundation\System\Helper::replaceController($old_class, $new_class);
    }
    public static function getViewData(): array
    {
        return \DuckPhp\Foundation\System\Helper::getViewData();
    }
    public static function DbCloseAll()
    {
        return \DuckPhp\Foundation\System\Helper::DbCloseAll();
    }
    public static function SESSION($key = null, $default = null)
    {
        return \DuckPhp\Foundation\System\Helper::SESSION($key, $default);
    }
    public static function FILES($key = null, $default = null)
    {
        return \DuckPhp\Foundation\System\Helper::FILES($key, $default);
    }
    public static function SessionSet($key, $value)
    {
        return \DuckPhp\Foundation\System\Helper::SessionSet($key, $value);
    }
    public static function SessionUnset($key)
    {
        return \DuckPhp\Foundation\System\Helper::SessionUnset($key);
    }
    public static function SessionGet($key, $default = null)
    {
        return \DuckPhp\Foundation\System\Helper::SessionGet($key, $default);
    }
    public static function CookieSet($key, $value, $expire = 0)
    {
        return \DuckPhp\Foundation\System\Helper::CookieSet($key, $value, $expire);
    }
    public static function CookieGet($key, $default = null)
    {
        return \DuckPhp\Foundation\System\Helper::CookieGet($key, $default);
    }
    public static function system_wrapper_replace(array $funcs)
    {
        return \DuckPhp\Foundation\System\Helper::system_wrapper_replace($funcs);
    }
    public static function system_wrapper_get_providers(): array
    {
        return \DuckPhp\Foundation\System\Helper::system_wrapper_get_providers();
    }
    public static function set_exception_handler(callable $exception_handler)
    {
        return \DuckPhp\Foundation\System\Helper::set_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return \DuckPhp\Foundation\System\Helper::register_shutdown_function($callback, ...$args);
    }
    public static function session_start(array $options = [])
    {
        return \DuckPhp\Foundation\System\Helper::session_start($options);
    }
    public static function session_id($session_id = null)
    {
        return \DuckPhp\Foundation\System\Helper::session_id($session_id);
    }
    public static function session_destroy()
    {
        return \DuckPhp\Foundation\System\Helper::session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return \DuckPhp\Foundation\System\Helper::session_set_save_handler($handler);
    }
    public static function mime_content_type($file)
    {
        return \DuckPhp\Foundation\System\Helper::mime_content_type($file);
    }
    public static function setBeforeGetDbHandler($db_before_get_object_handler)
    {
        return \DuckPhp\Foundation\System\Helper::setBeforeGetDbHandler($db_before_get_object_handler);
    }
    public static function Redis($tag = 0)
    {
        return \DuckPhp\Foundation\System\Helper::Redis($tag);
    }
    public static function getRouteMaps()
    {
        return \DuckPhp\Foundation\System\Helper::getRouteMaps();
    }
    public static function assignRoute($key, $value = null)
    {
        return \DuckPhp\Foundation\System\Helper::assignRoute($key, $value);
    }
    public static function assignImportantRoute($key, $value = null)
    {
        return \DuckPhp\Foundation\System\Helper::assignImportantRoute($key, $value);
    }
    public static function assignRewrite($key, $value = null)
    {
        return \DuckPhp\Foundation\System\Helper::assignRewrite($key, $value);
    }
    public static function getRewrites()
    {
        return \DuckPhp\Foundation\System\Helper::getRewrites();
    }
    public static function getCliParameters()
    {
        return \DuckPhp\Foundation\System\Helper::getCliParameters();
    }
    public static function saveExtOptions(array $options): void
    {
        \DuckPhp\Foundation\System\Helper::saveExtOptions($options);
    }
    public static function ProjectThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return \DuckPhp\Foundation\System\Helper::ProjectThrowOn($flag, $message, $code, $exception_class);
    }
    public static function ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return \DuckPhp\Foundation\System\Helper::ThrowOn($flag, $message, $code, $exception_class);
    }
}
