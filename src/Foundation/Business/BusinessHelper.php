<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Foundation\Business;

use DuckPhp\Component\Cache;
use DuckPhp\Component\Configer;
use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\Validator;
use DuckPhp\Core\App;
use DuckPhp\Core\CoreHelper;
use DuckPhp\Core\SingletonExTrait;
use DuckPhp\GlobalAdmin\Admin;
use DuckPhp\GlobalUser\User;

class BusinessHelper
{
    use SingletonExTrait;

    const EVENT_SERVICE_USER_REGISTERING = User::EVENT_SERVICE_USER_REGISTERING;
    const EVENT_SERVICE_USER_REGISTERED = User::EVENT_SERVICE_USER_REGISTERED;
    const EVENT_SERVICE_USER_LOGINING = User::EVENT_SERVICE_USER_LOGINING;
    const EVENT_SERVICE_USER_LOGINED = User::EVENT_SERVICE_USER_LOGINED;
    const EVENT_SERVICE_USER_LOGOUTING = User::EVENT_SERVICE_USER_LOGOUTING;
    const EVENT_SERVICE_USER_LOGOUTED = User::EVENT_SERVICE_USER_LOGOUTED;

    const EXCEPTION_CODE_USER_NEED_LOGIN = User::EXCEPTION_CODE_USER_NEED_LOGIN;
    const EXCEPTION_MESSAGE_USER_NEED_LOGIN = User::EXCEPTION_MESSAGE_USER_NEED_LOGIN;
    const EXCEPTION_CODE_USER_NEED_PERMISSION = User::EXCEPTION_CODE_USER_NEED_PERMISSION;
    const EXCEPTION_MESSAGE_USER_NEED_PERMISSION = User::EXCEPTION_MESSAGE_USER_NEED_PERMISSION;

    const EVENT_SERVICE_ADMIN_LOGINING = Admin::EVENT_SERVICE_ADMIN_LOGINING;
    const EVENT_SERVICE_ADMIN_LOGINED = Admin::EVENT_SERVICE_ADMIN_LOGINED;
    const EVENT_SERVICE_ADMIN_LOGOUTING = Admin::EVENT_SERVICE_ADMIN_LOGOUTING;
    const EVENT_SERVICE_ADMIN_LOGOUTED = Admin::EVENT_SERVICE_ADMIN_LOGOUTED;

    const EXCEPTION_CODE_ADMIN_NEED_LOGIN = Admin::EXCEPTION_CODE_ADMIN_NEED_LOGIN;
    const EXCEPTION_MESSAGE_ADMIN_NEED_LOGIN = Admin::EXCEPTION_MESSAGE_ADMIN_NEED_LOGIN;
    const EXCEPTION_CODE_ADMIN_NEED_PERMISSION = Admin::EXCEPTION_CODE_ADMIN_NEED_PERMISSION;
    const EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION = Admin::EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION;


    public static function Setting($key = null, $default = null)
    {
        return App::_()->_Setting($key, $default);
    }
    public static function AppOptions(string $key, $default = null)
    {
        return App::_()->options[$key] ?? $default;
    }
    public static function Config($file_basename, $key = null, $default = null)
    {
        return Configer::_()->_Config($file_basename, $key, $default);
    }
    public static function XpCall($callback, ...$args)
    {
        return CoreHelper::_()->_XpCall($callback, ...$args);
    }
    public static function BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return CoreHelper::_()->_BusinessThrowOn($flag, $message, $code, $exception_class);
    }
    public static function ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return CoreHelper::_()->_BusinessThrowOn($flag, $message, $code, $exception_class);
    }

    public static function Cache($object = null)
    {
        return Cache::_($object);
    }
    public static function PathOfProject(): string
    {
        return App::_()->getProjectPath();
    }
    public static function PathOfRuntime(): string
    {
        return App::_()->getRuntimePath();
    }
    public static function FireGlobalEvent($event, ...$args)
    {
        return GlobalEvent::_()->fire($event, ...$args);
    }
    public static function OnGlobalEvent($event, $callback)
    {
        return GlobalEvent::_()->on($event, $callback);
    }
    /**
     * @return \DuckPhp\GlobalAdmin\AdminServiceInterface
     */
    public static function AdminService()
    {
        return Admin::_()->service();
    }
    /**
     * @return \DuckPhp\GlobalUser\UserServiceInterface
     */
    public static function UserService()
    {
        return User::_()->service();
    }
    //////////////////////
    // Validator data validation
    /**
     * Get or set the Validator instance.
     * @param \DuckPhp\Component\Validator|null $new
     * @return \DuckPhp\Component\Validator
     */
    public static function Validator($new = null)
    {
        return Validator::_($new);
    }
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     * @param array<string, string> $messages
     * @return array<string, mixed>
     */
    public static function ValidatorFilter($data, $rules, $messages = [])
    {
        return Validator::_()->init($rules)->setMessage($messages)->filter($data);
    }
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     * @param array<string, string> $messages
     */
    public static function ValidatorCheck($data, $rules, $messages = [])
    {
        Validator::_()->init($rules)->setMessage($messages)->check($data);
    }
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     * @param array<string, string> $messages
     * @return array<string, string>
     */
    public static function ValidatorValid($data, $rules, $messages = [])
    {
        return Validator::_()->init($rules)->setMessage($messages)->valid($data);
    }
}
