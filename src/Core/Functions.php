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
if (! function_exists('__json')) {
    function __json(...$args)
    {
        //return App::Url(...$args);
    }
}

if (! function_exists('__display')) {
    function __display(...$args)
    {
        return App::Display(...$args);
    }
}
if (! function_exists('__var_dump')) {
    function __var_dump(...$args)
    {
        return App::var_dump(...$args);
    }
}

if (! function_exists('__trace_dump')) {
    function __trace_dump(...$args)
    {
        return App::TraceDump(...$args);
    }
}

if (! function_exists('__debug_log')) {
    function __debug_log(...$args)
    {
        return App::DebugLog(...$args);
    }
}

if (! function_exists('__db')) {
    function __db($tag=null)
    {
        return App::Db($tag);
    }
}
if (! function_exists('__db')) {
    function __dbw()
    {
        return App::DbForWrite();
    }
}
if (! function_exists('__db')) {
    function __dbr($)
    {
        return App::DbForRead();
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
