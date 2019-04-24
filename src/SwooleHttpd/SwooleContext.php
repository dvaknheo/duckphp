<?php
namespace DNMVCS\SwooleHttpd;

use DNMVCS\SwooleHttpd\SwooleSingleton;

class SwooleContext
{
    use SwooleSingleton;
    public $request=null;
    public $response=null;
    public $fd=-1;
    public $frame=null;
    
    public $shutdown_function_array=[];
    public function initHttp($request, $response)
    {
        $this->request=$request;
        $this->response=$response;
    }
    public function initWebSocket($frame)
    {
        $this->frame=$frame;
        $this->fd=$frame->fd;
    }
    public function cleanUp()
    {
        $this->request=null;
        $this->response=null;
        $this->fd=-1;
        $this->frame=null;
    }
    public function onShutdown()
    {
        $funcs=array_reverse($this->shutdown_function_array);
        foreach ($funcs as $v) {
            $func=array_shift($v);
            $func($v);
        }
        $this->shutdown_function_array=[];
    }
    public function regShutdown($call_data)
    {
        $this->shutdown_function_array[]=$call_data;
    }
    public function isWebSocketClosing()
    {
        return $this->frame->opcode == 0x08?true:false;
    }
    public function header(string $string, bool $replace = true, int $http_status_code =0)
    {
        if (!$this->response) {
            return;
        }
        if ($http_status_code) {
            $this->response->status($http_status_code);
        }
        if (strpos($string, ':')===false) {
            return;
        } // 404,500 so on
        list($key, $value)=explode(':', $string);
        $this->response->header($key, $value);
    }
    public function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain  = '', bool $secure = false, bool $httponly = false)
    {
        return $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
}
