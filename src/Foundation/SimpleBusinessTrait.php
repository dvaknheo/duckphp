<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\SingletonTrait;

trait SimpleBusinessTrait
{
    use SingletonTrait;
    
    public static function CallInPhase($phase)
    {
        return new PhaseProxy($phase, static::class);
    }
}
