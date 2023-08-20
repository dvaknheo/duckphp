<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Business;

use DuckPhp\Helper\BusinessHelper;
use DuckPhp\Singletonex\Singletonex;
use DuckPhp\ThrowOn\ThrowOnableTrait;

class BaseBusiness
{
    use Singletonex;
    use BusinessHelper;
    use ThrowOnableTrait;
}
