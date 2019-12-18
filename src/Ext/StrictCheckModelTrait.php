<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\SingletonEx;
use DuckPhp\StrictCheck;

trait StrictCheckModelTrait
{
    use SingletonEx {
        G as _ParentG;
    }
    public static function G($object = null)
    {
        StrictCheck::G()->checkStrictModel();
        return static::_ParentG($object);
    }
}
