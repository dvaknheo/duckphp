<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

interface PagerInterface
{
    public function current($new_value = null) : int;
    public function pageSize($new_value = null) : int;
    public function render($total, $options = []) : string;
}
