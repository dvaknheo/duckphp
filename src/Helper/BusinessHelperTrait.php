<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Component\Cache;
use DuckPhp\Component\EventManager;
use DuckPhp\Core\App;

trait BusinessHelperTrait
{
    public static function Setting($key)
    {
        return App::Setting($key);
    }
    public static function Config($file_basename, $key = null, $default = null)
    {
        return App::Config($file_basename, $key, $default);
    }
    public static function XpCall($callback, ...$args)
    {
        return App::XpCall($callback, ...$args);
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
