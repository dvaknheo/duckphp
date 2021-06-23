<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace SimpleBlog\System;

use DuckPhp\ThrowOn\ThrowOnTrait;
use Exception;
// use SimpleBlog\System\App;

class BaseException extends Exception
{
    use ThrowOnTrait;
    
    /*
    public function display($ex)
    {
        App::OnDefaultException($ex);
    }
    */
}
