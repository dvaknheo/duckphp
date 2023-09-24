<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Lazy;

use DuckPhp\Helper\BusinessHelperTrait;
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\ThrowOn\ThrowOnableTrait;


trait BusinessTrait
{
    use SingletonExTrait;
    use ThrowOnableTrait;
    use BusinessHelperTrait;
}