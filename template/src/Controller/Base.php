<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Controller;

use LazyToChange\System\BaseController;

class Base extends BaseController
{
    public function __construct()
    {
        if(self::getRouteCallingClass() === self::class){
            self::Exit404();
            return;
        }
        parent::__construct();
    }
}