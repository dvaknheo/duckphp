<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

use DuckPhp\Core\App;

if (! function_exists('__h')) {
    function __h(...$args)
    {
        return App::H(...$args);
    }
}
if (! function_exists('__l')) {
    function __l(...$args)
    {
        return App::L(...$args);
    }
}
if (! function_exists('__hl')) {
    function __hl(...$args)
    {
        return App::HL(...$args);
    }
}
if (! function_exists('__url')) {
    function __url(...$args)
    {
        return App::URL(...$args);
    }
}
if (! function_exists('__display')) {
    function __display(...$args)
    {
        return App::Display(...$args);
    }
}
