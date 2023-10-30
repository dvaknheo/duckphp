<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
use DuckPhp\Core\CoreHelper;

if (! function_exists('__h')) {
    function __h($str)
    {
        return CoreHelper::H($str);
    }
}
if (! function_exists('__l')) {
    function __l($str, $args = [])
    {
        return CoreHelper::L($str, $args);
    }
}
if (! function_exists('__hl')) {
    function __hl($str, $args = [])
    {
        return CoreHelper::Hl($str, $args);
    }
}
if (! function_exists('__json')) {
    function __json($data)
    {
        return CoreHelper::Json($data);
    }
}
if (! function_exists('__url')) {
    function __url($url)
    {
        return CoreHelper::Url($url);
    }
}
if (! function_exists('__domain')) {
    function __domain($use_scheme = false)
    {
        return CoreHelper::Domain($use_scheme);
    }
}
if (! function_exists('__res')) {
    function __res($url)
    {
        return CoreHelper::Res($url);
    }
}
//////////////////////////////////////////////////
if (! function_exists('__display')) {
    function __display(...$args)
    {
        return CoreHelper::Display(...$args);
    }
}
//////////////////////////////////////////////////
if (! function_exists('__var_dump')) {
    function __var_dump(...$args)
    {
        return CoreHelper::var_dump(...$args);
    }
}
if (! function_exists('__var_log')) {
    function __var_log($var)
    {
        return CoreHelper::VarLog($var);
    }
}
if (! function_exists('__trace_dump')) {
    function __trace_dump()
    {
        return CoreHelper::TraceDump();
    }
}
if (! function_exists('__debug_log')) {
    function __debug_log($str, $args = [])
    {
        return CoreHelper::DebugLog($str, ...$args);
    }
}
if (! function_exists('__logger')) {
    function __logger()
    {
        return CoreHelper::Logger();
    }
}
if (! function_exists('__is_debug')) {
    function __is_debug()
    {
        return CoreHelper::IsDebug();
    }
}
if (! function_exists('__is_real_debug')) {
    function __is_real_debug()
    {
        return CoreHelper::IsRealDebug();
    }
}
if (! function_exists('__platform')) {
    function __platform()
    {
        return CoreHelper::Platform();
    }
}
