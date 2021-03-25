<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Base;

use DuckPhp\ThrowOn\ThrowOnTrait;

class BaseException extends \Exception
{
    use ThrowOnTrait;
}
