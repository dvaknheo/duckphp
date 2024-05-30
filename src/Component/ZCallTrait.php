<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Component\PhaseProxy;

trait ZCallTrait
{
    /**
     * @return self
     */
    public static function _Z($phase)
    {
        return PhaseProxy::CreatePhaseProxy($phase, static::class);
    }
}
