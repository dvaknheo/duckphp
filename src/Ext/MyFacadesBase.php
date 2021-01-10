<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Ext\MyFacadesAutoLoader;

class MyFacadesBase extends ComponentBase
{
    public function __construct()
    {
    }
    public static function __callStatic($name, $arguments)
    {
        $callback = MyFacadesAutoLoader::G()->getFacadesCallback(static::class, $name);
        if (!$callback) {
            throw new \ErrorException("BadCall");
        }
        $ret = call_user_func_array($callback, $arguments);
        return $ret;
    }
}
