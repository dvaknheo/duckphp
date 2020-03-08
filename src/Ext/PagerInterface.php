<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

interface PagerInterface
{
    public function current();
    public function pageSize($new_value = null);
    public function render($total, $options = []);
}
