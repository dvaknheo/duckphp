<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Lazy;

use DuckPhp\Component\ControllerFakeSingletonTrait;
use DuckPhp\Helper\ControllerHelperTrait;
use DuckPhp\ThrowOn\ThrowOnableTrait;


trait ControllerTrait
{
    use ControllerFakeSingletonTrait;
    use ControllerHelperTrait;
    use ThrowOnableTrait;
}