<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\PhaseContainer;

trait SingletonTrait
{
    public static function _($object = null)
    {
        return PhaseContainer::GetObject(static::class, $object);
    }
}
