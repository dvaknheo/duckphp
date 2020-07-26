<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\SuperGlobal;

use Workerman\Protocols\Http\Session;

// @codeCoverageIgnoreStart
class PluginForWorkerMan extends ComponentBase  
{

    public $options = [
        'workerman'=>[],
    ];
    public function init(array $options, object $context = null)
    {
        if (PHP_SAPI !== 'cli') {
            return $this; // @codeCoverageIgnore
        }
        
        if (method_exists($context, 'extendComponents')) {
            $context->extendComponents(
                [
                    'RunWorkerMan' => [static::class, 'RunWorkerMan'],
                    'OnWorkmanMessage' => [static::class, 'OnWorkmanMessage'],
                    'WorkermanRequest' => [static::class, 'WorkermanRequest'],
                    'WorkermanResponse' => [static::class, 'WorkermanResponse'],
                ],
                ['A']
            );
        }
        $context::G()->system_wrapper_replace([
            'header' => [static::class,'header'],
            'setcookie' => [static::class,'setcookie'],
            'exit' => [static::class,'exit'],
            'register_shutdown_function' => [static::class,'register_shutdown_function'],
        ]);
    }
    public static function RunWorkerMan($connection, $request)
    {
         ob_start(function ($str) use ($connection) {
            $connection->send($str);
        });
        
        ($callback)();
        
        ob_end_flush();
    }
    
    
    public static function OnWorkmanMessage($connection, $request, $callback)
    {
        return static::G()->onMessage($connection, $request, $callback);
    }
    public static function WorkermanRequest()
    {
        return WorkermanRequest::G();
    }
    public static function WorkermanResponse()
    {
        return WorkermanResponse::G();
    }
    ///////////////////
    public function onMessage($connection, $request, $callback)
    {        
        ob_start(function ($str) use ($connection) {
            $connection->send($str);
        });
        
        ($callback)();
        
        ob_end_flush();
    }
}
trait Workerman_SystemWrapper
{
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    {
        return static::G()->_header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return static::G()->_setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit($code = 0)
    {
        return static::G()->_exit($code);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return static::G()->_register_shutdown_function($callback, ...$args);
    }
    ////
    public function _header($output, bool $replace = true, int $http_response_code = 0)
    {
        WorkermanResponse::G()->->header($name, $value);
    }
    public function _setcookie($key, $value, $max_age, $path, $domain, $secure, $http_only)
    {
        WorkermanResponse::G()->cookie($key, $value, $max_age, $path, $domain, $secure, $http_only);
    }

    public function _exit($code = 0)
    {
        throw new WorkermanExitException('',$code);
    }
    public function _register_shutdown_function(callable $callback, ...$args)
    {
        //
    }
}
class WorkermanSuperGlobal extends SuperGlobal
{
    public function initWithRequest($request)
    {
        //$superglobal = 
        $this->_GET = $request->get();
        $this->_POST = $request->post();
        $this->_REQUEST = array_merge($this->_GET, $this->_POST);
        
        $this->_COOKIE = $request->cookie();
        $this->_ENV = $_ENV;
        $this->_SESSION = '';
        //$this->_FILES=;
        $this->_SERVER = [];
    }
    public function session_start(array $options = [])
    {
        //
    }
    public function session_id($session_id)
    {
        if($session_id ===null){
            //
        }
        return WorkermanSession::G()->getId();
    }
    public function session_destroy()
    {
        //
    }
    public function session_set_save_handler($handler)
    {
        WorkermanSession::handlerClass(get_class($handler));
    }
}
class WorkermanSession extends Session
{
    public $_data;
}
// @codeCoverageIgnoreEnd
