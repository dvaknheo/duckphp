<?php
namespace DNMVCS;

class SystemWrapper
{
	use DNSingleton;
	
	public $header_handler=null;
	public $cookie_handler=null;
	
	public static function header($output ,bool $replace = true , int $http_response_code=0)
	{
		if(static::G()->header_handler){
			return (static::G()->header_handler)($output,$replace,$http_response_code);
		}
		if(PHP_SAPI==='cli'){ return; }
		if(headers_sent()){ return; }
		return header($output,$replace,$http_response_code);
	}
	
	public static function setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)
	{
		if(static::G()->cookie_handler){
			return (static::G()->cookie_handler)($key,$value,$expire,$path,$domain,$secure,$httponly);
		}
		return setcookie($key,$value,$expire,$path,$domain,$secure,$httponly);
	}
	public function exit_system($code)
	{
		exit;
	}
}