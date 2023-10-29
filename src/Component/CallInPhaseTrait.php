<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Component\PhaseProxy;

trait CallInPhaseTrait
{
    public static function CallInPhase($phase)
    {
        return new PhaseProxy($phase, static::class);
    }
}
