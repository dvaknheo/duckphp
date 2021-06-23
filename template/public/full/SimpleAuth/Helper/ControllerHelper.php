<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Helper;

use DuckPhp\Helper\ControllerHelper as Helper;
use SimpleAuth\System\App;

class ControllerHelper extends Helper
{
    public static function SessionManager()
    {
        return App::SessionManager();
    }
}
