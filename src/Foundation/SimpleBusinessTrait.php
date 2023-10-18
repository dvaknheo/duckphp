<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Foundation\CallInPhaseTrait;
use DuckPhp\SingletonEx\SingletonExTrait;

trait SimpleBusinessTrait
{
    use SingletonExTrait;
    use CallInPhaseTrait;
}
