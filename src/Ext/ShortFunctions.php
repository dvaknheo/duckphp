<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */

if (! function_exists('e')) {
    function e($str)
    {
        return \DuckPhp\Core\App::H($str);
    }
}
