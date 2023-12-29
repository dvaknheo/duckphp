<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Component\Cache;
use DuckPhp\Component\Configer;
use DuckPhp\Core\App;
use DuckPhp\Core\CoreHelper;
use DuckPhp\Core\EventManager;
use DuckPhp\Core\SingletonTrait;

trait BusinessHelperTrait
{
    use SingletonTrait;
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
    public static function ThrowByFlag($exception, $flag, $message, $code = 0)
    {
        return CoreHelper::_()->_ThrowByFlag($exception, $flag, $message, $code);
    }
    public static function ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return CoreHelper::_()->_ThrowOn($flag, $message, $code, $exception_class);
    }
    public static function Cache($object = null)
    {
        return Cache::_($object);
    }
    public static function FireEvent($event, ...$args)
    {
        return EventManager::FireEvent($event, ...$args);
    }
    public static function OnEvent($event, $callback)
    {
        return EventManager::OnEvent($event, $callback);
    }
}
