<?php
namespace DNMVCS\Ext;

use DNMVCS\DNSingleton;
use FacadesAutoLoader;

class FacadesBase
{
    use DNSingleton;
    
    public static function __callStatic($name, $arguments)
    {
        $callback=FacadesAutoLoader::G()->getFacadesCallback(static::class, $name);
        $ret=call_user_func_array($callback, $arguments);
        return $ret;
    }
}
