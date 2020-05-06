<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\SingletonEx;

class RuntimeState
{
    use SingletonEx;
    
    protected $is_running = false;
    protected $is_in_exception = false;
    protected $is_outputed = false;
    protected $is_inited = false;
    public function __construct()
    {
    }
    public static function ReCreateInstance()
    {
        $class = get_class(static::G());
        return static::G(new $class);
    }
    public function init(array $options, object $context = null)
    {
        $this->is_inited = true;
        return $this;
    }
    public function isInited():bool
    {
        return $this->is_inited;
    }
    
    public function begin()
    {
        $this->is_running = true;
    }
    public function end()
    {
        $this->is_in_exception = false;
        $this->is_running = false;
    }
    public function isRunning()
    {
        return $this->is_running;
    }

    public function toggleInException($flag = true)
    {
        $this->is_in_exception = $flag;
    }
    public function isInException()
    {
        return $this->is_in_exception;
    }
    public function isOutputed()
    {
        return $this->is_outputed;
    }
    public function toggleOutputed($flag = true)
    {
        $this->is_outputed = true;
    }
}
