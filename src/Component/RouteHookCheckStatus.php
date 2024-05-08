<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\DuckPhpSystemException;
use DuckPhp\Core\Route;
use DuckPhp\Core\View;

class RouteHookCheckStatus extends ComponentBase
{
    public $options = [
        'need_install' => false,
        'is_maintaining' =>false,
        'maintain_view' => null,
    ];
    public static function Hook($path_info)
    {
        return static::_()->doHook($path_info);
    }
    //@override
    protected function initContext(object $context)
    {
        Route::_()->addRouteHook([static::class,'Hook'], 'prepend-outter');
    }

    public function doHook($path_info)
    {
        $flag = App::Setting('duckphp_is_maintaining',false)  || $this->options['is_maintaining'];
        if ($flag){
            if ($this->options['maintain_view']) {
                View::Show([],$this->options['maintain_view']);
                return true;
            }else{
                DuckPhpSystemException::ThrowOn(true, 'Maintainning');
            }
        }
        
        $flag = $this->options['need_install'] && !App::Current()->isInstalled();
        DuckPhpSystemException::ThrowOn($flag, 'Need install');
        return false;
    }
}
