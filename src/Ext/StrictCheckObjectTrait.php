<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\SingletonEx;
use DuckPhp\Ext\StrictCheck;

trait StrictCheckObjectTrait
{
    use SingletonEx {
        G as _ParentG;
    }
    public static function G($object = null)
    {
        StrictCheck::G()->checkStrictClass(static::class, 2);
        return static::_ParentG($object);
    }
}
