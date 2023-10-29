<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Component\CallInPhaseTrait;
use DuckPhp\Core\SingletonTrait;

trait SimpleActionTrait
{
    use SingletonTrait;
    use CallInPhaseTrait;
}
