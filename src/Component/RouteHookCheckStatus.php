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
        //'need_install' => false,
        //'is_maintain' =>false,
        'maintain_view' => null,
        'need_install_view' => null,
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
        if (App::Setting('duckphp_is_maintain', false) || (App::Current()->options['is_maintain'] ?? false)) {
            if ($this->options['maintain_view'] ?? false) {
                View::Show([], $this->options['maintain_view']);
                return true;
            } else {
                DuckPhpSystemException::ThrowOn(true, 'Maintainning');
            }
        }
        if ((App::Current()->options['need_install'] ?? false) && !App::Current()->isInstalled()) {
            if ($this->options['need_install_view'] ?? false) {
                View::Show([], $this->options['need_install_view']);
                return true;
            } else {
                DuckPhpSystemException::ThrowOn(true, 'Need Install');
            }
        }
    }
}
