<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Lazy;

use DuckPhp\Helper\ControllerHelperTrait;
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\ThrowOn\ThrowOnableTrait;


trait ActionTrait
{
    use SingletonExTrait;
    use ControllerHelperTrait;
    use ThrowOnableTrait;
}