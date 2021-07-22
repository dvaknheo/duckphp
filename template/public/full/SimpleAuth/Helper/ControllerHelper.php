<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Helper;

use DuckPhp\Helper\ControllerHelper as Helper;
use SimpleAuth\ControllerEx\SessionManager;

class ControllerHelper extends Helper
{
    public static function SessionManager()
    {
        return SessionManager::G();
    }
}
