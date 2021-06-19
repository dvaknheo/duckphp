<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;

class ExceptionManager extends ComponentBase
{
    public $options = [
        'handle_all_dev_error' => true,
        'handle_all_exception' => true,
        'system_exception_handler' => null,
        
        'default_exception_handler' => null,
        'dev_error_handler' => null,
    ];
    
    protected $exceptionHandlers = [];
    protected $default_exception_handler = null;
    
    protected $system_exception_handler = null;
    protected $last_error_handler = null;
    protected $last_exception_handler = null;
    
    public $is_running = false;

    public static function CallException($ex)
    {
        return static::G()->_CallException($ex);
    }
    public function setDefaultExceptionHandler($default_exception_handler)
    {
        $this->default_exception_handler = $default_exception_handler;
    }
    public function assignExceptionHandler($class, $callback = null)
    {
        $class = is_string($class)?array($class => $callback):$class;
        foreach ($class as $k => $v) {
            $this->exceptionHandlers[$k] = $v;
        }
    }
    public function setMultiExceptionHandler(array $classes, $callback)
    {
        foreach ($classes as $class) {
            $this->exceptionHandlers[$class] = $callback;
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
            ($this->options['dev_error_handler'])($errno, $errstr, $errfile, $errline);
            break;
        default:
            throw new \ErrorException($errstr, $errno, $errno, $errfile, $errline);
        }
        /* Don't execute PHP internal error handler */
        return true;
    }
    public function _CallException($ex)
    {
        $t = $this->exceptionHandlers;
        $t = array_reverse($t);
        foreach ($t as $class => $callback) {
            if (is_a($ex, $class)) {
                ($callback)($ex);
                return;
            }
        }
        if ($this->default_exception_handler) {
            ($this->default_exception_handler)($ex);
        }
    }
    
    //@override
    protected function initOptions(array $options)
    {
        $this->default_exception_handler = $this->options['default_exception_handler'];
        $this->system_exception_handler = $this->options['system_exception_handler'];
    }
    public function isInited():bool
    {
        return $this->is_inited;
    }
    public function run()
    {
        if ($this->is_running) {
            return;
        }
        $this->is_running = true;
        
        if ($this->options['handle_all_dev_error']) {
            $this->last_error_handler = set_error_handler([$this,'on_error_handler']);
        }
        
        if ($this->options['handle_all_exception']) {
            if ($this->system_exception_handler) {
                $this->last_exception_handler = ($this->system_exception_handler)([$this,'_CallException']);
            } else {
                /** @var mixed */
                $handler = [static::class,'CallException'];
                $this->last_exception_handler = set_exception_handler($handler);
            }
        }
    }
    public function reset()
    {
        // $this->exceptionHandlers = [];
        // $this->default_exception_handler = null;
        return $this;
    }
    public function clear()
    {
        if ($this->options['handle_all_dev_error']) {
            restore_error_handler();
        }
        if ($this->options['handle_all_exception']) {
            if ($this->system_exception_handler) {
                $this->system_exception_handler = null;
            } else {
                restore_exception_handler();
            }
        }
        $this->is_running = false;
        $this->is_inited = false;
    }
}
