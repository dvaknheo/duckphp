<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace MY\Base;

use DuckPhp\ThrowOn\ThrowOn;
use Exception;

class BaseException extends Exception
{
    use ThrowOn;
}
