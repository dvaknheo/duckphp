<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ThrowOnTrait;
use Exception;

class DuckPhpSystemException extends Exception
{
    use ThrowOnTrait;
}
