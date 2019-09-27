<?php
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class ExceptionManager
{
    use SingletonEx;
    
    const DEFAULT_OPTIONS=[
        'exception_handler'=>null,
        'dev_error_handler'=>null,
        'system_exception_handler'=>null,
    ];
    protected $errorHandlers=[];
    protected $dev_error_handler=null;
    protected $exception_error_handler=null;

    protected $exception_handler=null;
    protected $exception_error_handler_init=null;
    protected $system_exception_handler=null;
    protected $last_error_handler=null;
    protected $last_exception_handler=null;
    
    public $is_inited=false;
    public $is_running=false;
    
    public function setDefaultExceptionHandler($default_exception_handler)
    {
        return $this->exception_error_handler=$default_exception_handler;
    }
    public function assignExceptionHandler($class, $callback=null)
    {
        $class=is_string($class)?array($class=>$callback):$class;
        foreach ($class as $k=>$v) {
            $this->errorHandlers[$k]=$v;
        }
    }
    public function setMultiExceptionHandler(array $classes, $callback)
    {
        foreach ($classes as $class) {
            $this->errorHandlers[$class]=$callback;
        }
    }
    public function on_error_handler($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        switch ($errno) {
        case E_USER_NOTICE:
        case E_NOTICE:
        case E_STRICT:
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            ($this->dev_error_handler)($errno, $errstr, $errfile, $errline);
            break;
        default:
            throw new \ErrorException($errstr, $errno, $errno, $errfile, $errline);
                //TODO test more in swoole;
                break;
        }
        /* Don't execute PHP internal error handler */
        return true;
    }
    public function checkAndRunErrorHandlers($ex, $inDefault)
    {
        $exception_class=get_class($ex);
        foreach ($this->errorHandlers as $class =>$callback) {
            if ($class===$exception_class) {
                ($callback)($ex);
                return true;
            }
        }
        if ($inDefault) {
            //TODO remove the dead code
            if ($this->exception_error_handler != $this->exception_error_handler_init) {
                ($this->exception_error_handler)($ex);
                return true;
            }
            // 
        }
        
        return false;
    }
    public function on_exception($ex)
    {
        $flag=$this->checkAndRunErrorHandlers($ex, false);
        if ($flag) {
            return;
        }
        ($this->exception_error_handler)($ex);
    }
    public function init($options=[], $context=null)
    {
        if ($this->is_inited) {
            return $this;
        }
        $this->is_inited=true;
        
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, $options);
        
        $exception_handler=$options['exception_handler'];
        $this->dev_error_handler=$options['dev_error_handler'];
        $this->system_exception_handler=$options['system_exception_handler'];
        
        $this->exception_handler=$exception_handler;
        $this->exception_error_handler=$exception_handler;
        $this->exception_error_handler_init=$exception_handler;
        
        return $this;
    }
    public function run()
    {
        if ($this->is_running) {
            return;
        }
        $this->is_running=true;
        
        $this->last_error_handler=set_error_handler([$this,'on_error_handler']);
        if ($this->system_exception_handler) {
            $this->last_exception_handler=($this->system_exception_handler)($this->exception_handler);
        } else {
            $this->last_exception_handler=set_exception_handler([$this,'on_exception']);
        }
    }
    public function cleanUp()
    {
        restore_error_handler();
        if ($this->system_exception_handler) {
            $this->system_exception_handler=null;
        } else {
            restore_exception_handler();
        }
        $this->is_running=false;
        $this->is_inited=false;
    }
}
