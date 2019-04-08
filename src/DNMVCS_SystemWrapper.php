<?php
namespace DNMVCS;

use DNMVCS\DNSuperGlobal;

trait DNMVCS_SystemWrapper
{
    public $header_handler=null;
    public $cookie_handler=null;
    public $exit_handler=null;
    public $exception_handler=null;
    public $shutdown_handler=null;

    public static function header($output, bool $replace = true, int $http_response_code=0)
    {
        return static::G()->_header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain  = '', bool $secure = false, bool $httponly = false)
    {
        return static::G()->_setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit_system($code=0)
    {
        return static::G()->_exit_system($code);
    }
    
    public static function set_exception_handler(callable $exception_handler)
    {
        return static::G()->_set_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return static::G()->_register_shutdown_function($callback, ...$args);
    }
    
    public static function session_start(array $options=[])
    {
        return DNSuperGlobal::G()->session_start($options);
    }
    public static function session_destroy()
    {
        return DNSuperGlobal::G()->session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return DNSuperGlobal::G()->session_set_save_handler($handler);
    }
    
    public function _header($output, bool $replace = true, int $http_response_code=0)
    {
        if ($this->header_handler) {
            return ($this->header_handler)($output, $replace, $http_response_code);
        }
        if (PHP_SAPI==='cli') {
            return;
        }
        if (headers_sent()) {
            return;
        }
        return header($output, $replace, $http_response_code);
    }
    
    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain  = '', bool $secure = false, bool $httponly = false)
    {
        if ($this->cookie_handler) {
            return ($this->cookie_handler)($key, $value, $expire, $path, $domain, $secure, $httponly);
        }
        return setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public function _exit_system($code=0)
    {
        if ($this->exit_handler) {
            return ($this->exit_handler)($code);
        }
        exit($code);
    }
    public function _set_exception_handler(callable $exception_handler)
    {
        if ($this->exception_handler) {
            return ($this->exception_handler)($exception_handler);
        }
        return set_exception_handler($exception_handler);
    }
    public function _register_shutdown_function(callable $callback, ...$args)
    {
        if ($this->shutdown_handler) {
            return ($this->shutdown_handler)($callback, ...$args);
        }
        return register_shutdown_function($callback, ...$args);
    }
    public function system_wrapper_replace(array $funcs=[])
    {
        if (isset($funcs['header'])) {
            $this->header_handler=$funcs['header'];
        }
        if (isset($funcs['setcookie'])) {
            $this->cookie_handler=$funcs['setcookie'];
        }
        if (isset($funcs['exit_system'])) {
            $this->exit_handler=$funcs['exit_system'];
        }
        if (isset($funcs['set_exception_handler'])) {
            $this->exception_handler=$funcs['set_exception_handler'];
        }
        if (isset($funcs['register_shutdown_function'])) {
            $this->shutdown_handler=$funcs['register_shutdown_function'];
        }
        
        return true;
    }
    public static function system_wrapper_get_providers():array
    {
        $ret=[
            'header'				=>[static::class,'header'],
            'setcookie'				=>[static::class,'setcookie'],
            'exit_system'			=>[static::class,'exit_system'],
            'set_exception_handler'	=>[static::class,'set_exception_handler'],
            'register_shutdown_function' =>[static::class,'register_shutdown_function'],
            
            'super_global' =>[DNSuperGloabl::class,'G'],
        ];
        return $ret;
    }
}
