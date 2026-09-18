<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Core;

interface ComponentInterface
{
    //public $options; /* array() */;
    public static function _($new_object = null);
    /**
     * @param array<string, mixed> $options
     */
    public function init(array $options, ?object $context = null);/*return this */
    public function isInited():bool;
}
