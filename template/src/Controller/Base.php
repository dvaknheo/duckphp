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
        // 我们弄个小技巧，不允许直接访问，但我们可以创建一个实例填充，         
        if (BaseController::CheckRunningController(self::class, static::class)) {
            return;
        }
        parent::__construct();
    }
}