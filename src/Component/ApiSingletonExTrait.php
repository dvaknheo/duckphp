<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;

trait ApiSingletonExTrait
{
    use SingletonExTrait { G as _G; }
    //public static $App;
    public static function G($object = null)
    {
        if($object){
            return _G($object);
        }
        
        $phase = App::Phase();
        if(!$phase){
            return _G($object);
        }
        
        $base = get_class(App::G());
        if($phase === $base){
            //in root.
            return new PhaseProxy($base,static::App,true);
        }else{
            // in parent
            $ret = _G();
            static::$App = $phase;
            return $ret;
        }
    
        
    }
}

