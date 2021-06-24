<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\System;

use DuckPhp\ThrowOn\ThrowOnTrait;

class BaseException extends \Exception
{
    use ThrowOnTrait;

}
