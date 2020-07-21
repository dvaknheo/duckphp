<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\SuperGlobal;

class PluginForWorkerMan extends ComponentBase
{
    public $options = [
        //
    ];
    public function init(array $options, $context = null)
    {
        if (method_exists($context, 'extendComponents')) {
            $context->extendComponents(
                [
                    'OnWorkmanMessage' => [static::class, 'OnWorkmanMessage'],
                    'WorkermanRequest' => [static::class, 'WorkermanRequest'],
                    'WorkermanResponse' => [static::class, 'WorkermanResponse'],
                ],
                ['A']
            );
        }
        /*
        DuckPhp::G()->system_wrapper_get_providers([
            'header' => null,
            'setcookie' => null,
            'exit' => null,
            'register_shutdown_function' => null,

            'session_start' => null,
            'session_id' => null,
            'session_destroy' => null,
            'session_set_save_handler' => null,
        ]);
        */
    }
    public static function OnWorkmanMessage($connection, $request, $callback)
    {
        return static::G()->onMessage($connection, $request, $callback);
    }
    public static function WorkermanRequest()
    {
        //
    }
    public static function WorkermanResponse()
    {
        //
    }
    public function header()
    {
    }
    public function setcookie()
    {
    }
    public function exit()
    {
    }
    public function register_shutdown_function()
    {
    }
    
    public function session_start()
    {
        //
    }
    public function session_id()
    {
        //
    }
    public function session_destroy()
    {
        //
    }
    public function session_set_save_handler()
    {
        //
    }
    
    ///////////////////
    
    public function initWithRequest($request)
    {
        $sg=
        $superglobal->_GET = $request->get();
        $superglobal->_POST = $request->post();
        $superglobal->_REQUEST = array_merge($this->_GET, $this->_POST);
        
        $superglobal->_COOKIE = $request->cookie();
        $superglobal->_ENV = $_ENV;
        $superglobal->_SESSION = '';
        //$this->_FILES=;
        $superglobal->_SERVER=;
    }
    public function onMessage($connection, $request, $callback)
    {
        //$request->get();

        //$request->post();
        //$request->header();
        //$request->cookie();
        //$requset->session();
        //$request->uri();
        //$request->path();
        //$request->method();
        // send data to client
        
        ob_start(function ($str) use ($connection) {
            $connection->send($str);
        });
        
        ($callback)();
        
        ob_end_flush();
    }
}

class WorkermanSession implements \ArrayAccess
{
    private $container = array();
    public function __construct()
    {
        $this->container = array(
            "one" => 1,
            "two" => 2,
            "three" => 3,
        );
    }
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}
