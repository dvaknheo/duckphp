<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
use DuckPhp\Core\App;

if (! function_exists('e')) {
    function e($str)
    {
        return App::H($str);
    }
}
if (! function_exists('view')) {
    function view($view, $data = null)
    {
        return App::ShowBlock($view, $data);
    }
}
