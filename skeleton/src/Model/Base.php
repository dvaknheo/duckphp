<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace YourProjectName\Model;

use DuckPhp\Foundation\SimpleModelTrait;
use DuckPhp\Helper\ModelHelperTrait;

abstract class Base
{
    use ModelHelperTrait;
    use SimpleModelTrait;
}
