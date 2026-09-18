<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalAdmin;

use DuckPhp\Core\ThrowOnTrait;
use Exception;

class AdminException extends Exception
{
    use ThrowOnTrait;
}
