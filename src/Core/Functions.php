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
        return App::Hl(...$args);
    }
}
if (! function_exists('__url')) {
    function __url(...$args)
    {
        return App::Url(...$args);
    }
}
if (! function_exists('__display')) {
    function __display(...$args)
    {
        return App::Display(...$args);
    }
}
if (! function_exists('__trace_dump')) {
    function __trace_dump(...$args)
    {
        return App::trace_dump(...$args);
    }
}
if (! function_exists('__var_dump')) {
    function __var_dump(...$args)
    {
        return App::var_dump(...$args);
    }
}
if (! function_exists('__debug_log')) {
    function __debug_log(...$args)
    {
        return App::debug_log(...$args);
    }
}

if (! function_exists('__db')) {
    function __db(...$args)
    {
        return App::Db(...$args);
    }
}

if (! function_exists('__is_debug')) {
    function __is_debug(...$args)
    {
        return App::IsDebug();
    }
}

if (! function_exists('__is_real_debug')) {
    function __is_real_debug(...$args)
    {
        return App::IsRealDebug();
    }
}

if (! function_exists('__platform')) {
    function __platform(...$args)
    {
        return App::Platform();
    }
}
