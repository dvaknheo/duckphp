<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
use DuckPhp\Core\App;

if (! function_exists('__h')) {
    function __h($str)
    {
        return App::H($str);
    }
}
if (! function_exists('__l')) {
    function __l($str, $args = [])
    {
        return App::L($str, $args);
    }
}
if (! function_exists('__hl')) {
    function __hl($str, $args = [])
    {
        return App::Hl($str, $args);
    }
}
if (! function_exists('__url')) {
    function __url($url)
    {
        return App::Url($url);
    }
}
if (! function_exists('__json')) {
    function __json($data)
    {
        return App::Json($data);
    }
}
if (! function_exists('__domain')) {
    function __domain($use_scheme = false)
    {
        return App::Domain($use_scheme);
    }
}
//////////////////////////////////////////////////
if (! function_exists('__display')) {
    function __display(...$args)
    {
        return App::Display(...$args);
    }
}
//////////////////////////////////////////////////
if (! function_exists('__var_dump')) {
    function __var_dump(...$args)
    {
        return App::var_dump(...$args);
    }
}

if (! function_exists('__trace_dump')) {
    function __trace_dump()
    {
        return App::TraceDump();
    }
}
if (! function_exists('__logger')) {
    function __logger()
    {
        return App::Logger();
    }
}
if (! function_exists('__debug_log')) {
    function __debug_log($str, $args = [])
    {
        return App::DebugLog($str, ...$args);
    }
}

if (! function_exists('__is_debug')) {
    function __is_debug()
    {
        return App::IsDebug();
    }
}
if (! function_exists('__is_real_debug')) {
    function __is_real_debug()
    {
        return App::IsRealDebug();
    }
}

if (! function_exists('__platform')) {
    function __platform()
    {
        return App::Platform();
    }
}
