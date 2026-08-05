<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Helper;

use DuckPhp\Component\Cache;
use DuckPhp\Component\Configer;
use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\Validator;
use DuckPhp\Core\App;
use DuckPhp\Core\CoreHelper;
use DuckPhp\Core\SingletonTrait;
use DuckPhp\GlobalAdmin\GlobalAdmin;
use DuckPhp\GlobalUser\GlobalUser;

trait BusinessHelperTrait
{
    use SingletonTrait;

    public static $EVENT_REGISTING = 'registing';
    public static $EVENT_REGISTED = 'registed';

    public static $EVENT_LOGINING = 'logining';
    public static $EVENT_LOGINED = 'logined';

    public static function Setting($key = null, $default = null)
    {
        return App::_()->_Setting($key, $default);
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
        return GlobalAdmin::_()->service();
    }
    /**
     * @return \DuckPhp\GlobalUser\UserServiceInterface
     */
    public static function UserService()
    {
        return GlobalUser::_()->service();
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
