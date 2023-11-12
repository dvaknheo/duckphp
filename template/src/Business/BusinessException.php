<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Business;

use DuckPhp\ThrowOn\ThrowOnTrait;

use ProjectNameTemplate\System\ProjectException;

class BusinessException extends ProjectException
{
    use ThrowOnTrait;
}
