<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Core\SingletonTrait;
use DuckPhp\Foundation\CallInPhaseTrait;

trait SimpleActionTrait
{
    use SingletonTrait;
    use CallInPhaseTrait;
}
