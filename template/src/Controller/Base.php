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
        parent:: __construct(self::class);
    }
}