<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\App;
use DuckPhp\SingletonEx\SingletonExTrait;

trait SimpleApiTrait
{
    use SingletonExTrait { G as _G; }
    //public static $AppClass;
    protected function GetAppClass()
    {
        if (isset(static::$AppClass)) {
            return  \get_class(static::$AppClass::G());
        } else {
            return '';
        }
    }
    public static function G($object = null)
    {
        $phase = App::Phase();
        if (!$phase) {
            return static::_G($object);
        }
        if (App::InRootPhase()) {
            $container = static::GetAppClass();
            if ($container && ($container === $phase || \is_subclass_of($phase, $container))) {
                $ret = static::_G($object);
                $ret->phase_fore_debug = $phase;
                return $ret;
            }
            return new PhaseProxy($container, static::class, false);
        } else {
            $ret = static::_G($object);
            $ret->phase_fore_debug = $phase;
            return $ret;
        }
    }
    public static function CallInPhase($phase)
    {
        return new PhaseProxy($phase, static::class);
    }
}
