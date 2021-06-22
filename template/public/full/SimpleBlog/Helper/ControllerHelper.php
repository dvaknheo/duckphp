<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace SimpleBlog\Helper;

use DuckPhp\Helper\ControllerHelper as Helper;
use SimpleBlog\System\Installer;
use SimpleBlog\System\SessionManager;

class ControllerHelper extends Helper
{
    // override or add your code here
    public static function CheckInstall()
    {
        $flag = Installer::G()->isInstalled();
        if (!$flag) {
            static::ExitRouteTo('install/index');
        }
    }
    public static function SessionManager()
    {
        return SessionManager::G();
    }
}
