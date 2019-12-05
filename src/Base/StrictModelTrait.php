<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Base;

use DuckPhp\Core\SingletonEx;
use DuckPhp\Core\App;

trait StrictModelTrait
{
    use SingletonEx {
        G as _ParentG;
    }
    public static function G($object = null)
    {
        App::G()->checkStrictModel();
        return static::_ParentG($object);
    }
}
