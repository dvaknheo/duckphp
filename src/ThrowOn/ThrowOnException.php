<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\ThrowOn;

use Exception;

class ThrowOnException extends Exception
{
    use ThrowOnTrait;
}
