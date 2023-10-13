<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace AdvanceDemo\Business;

use AdvanceDemo\System\ProjectException;

use DuckPhp\ThrowOn\ThrowOnTrait;

class BusinessException extends ProjectException
{
    use ThrowOnTrait;
}
