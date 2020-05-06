<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

interface ComponentInterface
{
    //public $options; /* array() */;
    public static function G($new_object = null);
    public function init(array $options, ?object $contetxt = null);/*return this */
    //public function isInited():bool;
}
