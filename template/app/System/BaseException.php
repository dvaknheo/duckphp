<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\System;

use DuckPhp\ThrowOn\ThrowOn;
use LazyToChange\System\App;

class BaseException
{
    use ThrowOn;
    
    public function display($ex)
    {
        App::OnDefaultException($ex);
    }
}
