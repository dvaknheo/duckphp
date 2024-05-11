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
        'error_maintain' => null,
        'error_need_install' => null,
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
            $error_maintain = $this->options['error_maintain'] ?? null;
            if (!is_string($error_maintain) && is_callable($error_maintain)) {
                ($error_maintain)();
                return true;
            }
            if (!$error_maintain) {
                $this->showMaintain();
                return true;
            }
            View::_(new View())->init(App::Current()->options, App::Current());
            View::Show([], $error_maintain);
            return true;
        }
        if ((App::Current()->options['need_install'] ?? false) && !App::Current()->isInstalled()) {
            $error_need_install = $this->options['error_need_install'] ?? null;
            if (!is_string($error_need_install) && is_callable($error_need_install)) {
                ($error_need_install)();
                return true;
            }
            if (!$error_need_install) {
                $this->showNeedInstall();
                return true;
            }
            View::_(new View())->init(App::Current()->options, App::Current());
            View::Show([], $error_need_install);
            return true;
        }
    }
    protected function showMaintain()
    {
        $str = <<<EOT
(Todo: a beautiful page ) Maintaining. <!-- set options['error_maintain'] to override -->
EOT;
        echo $str;
    }
    protected function showNeedInstall()
    {
        $str = <<<EOT
(Todo: a beautiful page ) Need Install. <!-- set options['error_need_install'] to override -->
EOT;
        echo $str;
    }
}
