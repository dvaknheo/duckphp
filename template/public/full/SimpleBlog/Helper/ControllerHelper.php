<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace SimpleBlog\Helper;

use DuckPhp\Helper\ControllerHelper as Helper;
use SimpleBlog\Business\InstallBusiness;

class ControllerHelper extends Helper
{
    // override or add your code here
    public static function CheckInstall()
    {
        $flag = InstallBusiness::G()->checkInstall();
        if (!$flag) {
            static::ExitRouteTo('install/index');
        }
    }
}
