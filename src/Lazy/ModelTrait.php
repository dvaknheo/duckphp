<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Lazy;

use DuckPhp\Component\SimpleModelTrait;
use DuckPhp\Helper\ModelHelperTrait;
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\ThrowOn\ThrowOnableTrait;


trait ModelTrait
{
    use SingletonExTrait;
    use ThrowOnableTrait;
    use ModelHelperTrait;
    use SimpleModelTrait;
}