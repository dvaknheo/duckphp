<?php
namespace DNMVCS\SwooleHttpd;

trait SwooleSingleton
{
    protected static $_instances=[];
    public static function G($object=null)
    {
        if (defined('DNMVCS_SINGLETONEX_REPALACER')) {
            $callback=DNMVCS_SINGLETONEX_REPALACER;
            return ($callback)(static::class, $object);
        }
        if ($object) {
            self::$_instances[static::class]=$object;
            return $object;
        }
        $me=self::$_instances[static::class]??null;
        if (null===$me) {
            $me=new static();
            self::$_instances[static::class]=$me;
        }
        return $me;
    }
}
