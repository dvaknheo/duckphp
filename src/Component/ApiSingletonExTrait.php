<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;
use DuckPhp\SingletonEx\SingletonExTrait;

trait ApiSingletonExTrait
{
    use SingletonExTrait { G as _G; }
    //public static $AppClass;
    public static function G($object = null)
    {
        if ($object) {
            return static::_G($object);
        }
        
        $phase = App::Phase();
        if (!$phase) {
            return static::_G($object);
        }
        
        $base = get_class(App::G());
        if ($phase === $base) {
            //in root.
            return new PhaseProxy(static::$AppClass, $base, false);
        } else {
            // in parent
            $ret = static::_G();
            static::$AppClass = $phase;
            return $ret;
        }
    }
}
