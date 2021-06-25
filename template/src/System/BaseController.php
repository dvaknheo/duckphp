<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\System;

use Duckphp\Helper\ControllerHelperTrait;
use Duckphp\SingletonEx\SingletonExTrait;

class BaseController
{
    use SingletonExTrait;
    use ControllerHelperTrait;
    
    protected $self_class = '';
    protected $is_helper =false;


    public function doCheckRunningController($helper,$self, $static)
    {
        if ($self === $static) {
            if ($self === static::getRouteCallingClass()) {
                static::Exit404();
            }
            return true;
        }
        if (method_exists($helper, static::getRouteCallingMethod()){
            static::Exit404();
        }
        return false;
    }
    
    public function __construct($base='')
    {
        $this->is_helper = $this->doCheckRunningController(self::class, $this->base , static::class);
        if($this->is_helper){
            return;
        }
        $this->initController();
    }
    protected function initController()
    {
        // real constructor();
        // do you work
    }
    public function __destroy()
    {
        if($this->is_helper){
            return;
        }
        // do you work
    }
    //////////////////////
    public static function SessionManager()
    {
        return SessionManager::G();
    }
}
