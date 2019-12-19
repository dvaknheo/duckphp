<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\SingletonEx;
use DuckPhp\Ext\StrictCheck;

trait StrictCheckServiceTrait
{
    use SingletonEx {
        G as _ParentG;
    }
    public static function G($object = null)
    {
        StrictCheck::G()->checkStrictService(static::class, 2);
        return static::_ParentG($object);
    }
}
