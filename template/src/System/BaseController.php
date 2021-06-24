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

    public static function SessionManager()
    {
        return SessionManager::G();
    }
}
