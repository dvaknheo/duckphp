<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Controller;

use LazyToChange\System\ProjectController;
use LazyToChange\System\App;

class Base extends ProjectController
{
    public function __construct()
    {
        self::CheckRunningController(self::class,static::class);

        parent::__construct();
    }
}