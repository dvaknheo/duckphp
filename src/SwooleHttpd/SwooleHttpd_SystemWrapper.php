<?php
namespace SwooleHttpd;

trait SwooleHttpd_SystemWrapper
{
    public static function header(string $string, bool $replace = true, int $http_status_code =0)
    {
        return SwooleContext::G()->header($string, $replace, $http_status_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain  = '', bool $secure = false, bool $httponly = false)
    {
        return SwooleContext::G()->setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit_system($code=0)
    {
        return static::G()->exit_request($code);
    }
    public static function set_exception_handler(callable $exception_handler)
    {
        return static::G()->set_http_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return SwooleContext::G()->regShutDown(func_get_args());
    }
    
    public static function session_start(array $options=[])
    {
        return SwooleSuperGlobal::G()->session_start($options);
    }
    public static function session_destroy()
    {
        return SwooleSuperGlobal::G()->session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return SwooleSuperGlobal::G()->session_set_save_handler($handler);
    }
    
    public static function system_wrapper_get_providers():array
    {
        $ret=[
            'header'				=>[static::class,'header'],
            'setcookie'				=>[static::class,'setcookie'],
            'exit_system'			=>[static::class,'exit_system'],
            'set_exception_handler'	=>[static::class,'set_exception_handler'],
            'register_shutdown_function' =>[static::class,'register_shutdown_function'],
        ];
        return $ret;
    }
}
