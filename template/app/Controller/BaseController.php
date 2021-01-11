<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Controller;

use LazyToChange\Helper\ControllerHelper as C;
use LazyToChange\System\SingletonEx;

class BaseController
{
    use SingletonEx;
    
    public function __construct()
    {
        // block direct visit
        if (static::class === self::class) {
            C::Exit404();
        }
    }
    public function foo()
    {
        var_dump(DATE(DATE_ATOM));
    }
}
