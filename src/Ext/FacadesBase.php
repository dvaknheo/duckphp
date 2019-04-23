<?php
namespace DNMVCS\Ext;

use DNMVCS\Basic\SingletonEx;
use DNMVCS\Ext\FacadesAutoLoader;

class FacadesBase
{
    use SingletonEx;
    
    public static function __callStatic($name, $arguments)
    {
        $callback=FacadesAutoLoader::G()->getFacadesCallback(static::class, $name);
        $ret=call_user_func_array($callback, $arguments);
        return $ret;
    }
}
