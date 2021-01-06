<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

interface PagerInterface
{
    public function current($new_value = null) : int;
    public function pageSize($new_value = null) : int;
    public function render($total, $options = []) : string;
}
