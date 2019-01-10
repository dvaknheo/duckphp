<?php
namespace DNMVCS;

class SystemWrapper
{
	use DNSingleton;
	
	public $header_handler=null;
	public $cookie_handler=null;
	
	public function header($output ,bool $replace = true , int $http_response_code=0)
	{
		if(static::G()->header_handler){
			return (static::G()->header_handler)($output,$replace,$http_response_code);
		}
		if(PHP_SAPI==='cli'){ return; }
		if(headers_sent()){ return; }
		return header($output,$replace,$http_response_code);
	}
	
	public function setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)
	{
		if(static::G()->cookie_handler){
			return (static::G()->cookie_handler)($key,$value,$expire,$path,$domain,$secure,$httponly);
		}
		return setcookie($key,$value,$expire,$path,$domain,$secure,$httponly);
	}
	public function set_exception_handler(callable $exception_handler)
	{
		//static::G()->http_exception_handler=$exception_handler;
	}
	public function register_shutdown_function(callable $callback,...$args)
	{
		//SwooleContext::G()->shutdown_function_array[]=func_get_args();
	}
	public function exitSystem($code)
	{
		//TODO
	}
}