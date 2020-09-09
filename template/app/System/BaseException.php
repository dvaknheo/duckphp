<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\System;

use DuckPhp\DuckPhp;
use DuckPhp\ThrowOn\ThrowOn;

class BaseException
{
    use ThrowOn;
    
    public function display($ex)
    {
        DuckPhp::OnDefaultException($ex);
    }
}
