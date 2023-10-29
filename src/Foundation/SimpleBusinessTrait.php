<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Foundation\CallInPhaseTrait;
use DuckPhp\Core\SingletonTrait;

trait SimpleBusinessTrait
{
    use SingletonTrait;
    use CallInPhaseTrait;
}
