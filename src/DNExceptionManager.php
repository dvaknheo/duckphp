<?php
namespace DNMVCS;

class DNExceptionManager
{
    use DNSingleton;
    
    protected $errorHandlers=[];
    protected $dev_error_handler=null;
    protected $exception_error_handler_init=null;
    protected $exception_error_handler=null;
    
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
        foreach ($classes as $k) {
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
            if ($this->exception_error_handler != $this->exception_error_handler_init) {
                ($this->exception_error_handler)($ex);
                return true;
            }
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
    public $is_inited=false;
    public function init($exception_handler, $dev_error_handler, $system_exception_handler=null)
    {
        if ($this->is_inited) {
            return;
        }
        $this->is_inited=true;
        $this->dev_error_handler=$dev_error_handler;
        $this->exception_error_handler=$exception_handler;
        $this->exception_error_handler_init=$exception_handler;
        
        set_error_handler([$this,'on_error_handler']);
        if ($system_exception_handler) {
            return ($system_exception_handler)($exception_handler);
        } else {
            set_exception_handler([$this,'on_exception']);
        }
    }
}
