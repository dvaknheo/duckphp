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
class WorkermanSuperGlobal extends SuperGlobal
{
    public $_GET;
    public $_POST;
    public $_REQUEST;
    public $_SERVER;
    public $_ENV;
    public $_COOKIE;
    public $_SESSION;
    public $_FILES;
    public function initWithRequest($request)
    {
        $this->_GET = $request->get();
        $this->_POST = $request->post();
        $this->_REQUEST = array_merge($this->_GET, $this->_POST);
        
        $this->_COOKIE = $request->cookie();
        $this->_ENV = $_ENV;
        //$this->_SERVER=;
        $this->_SESSION = '';
        //$this->_FILES=;
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
