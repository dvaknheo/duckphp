<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;

class RuntimeState extends ComponentBase
{
    public $options = [
    ];
    
    protected $is_running = false;
    protected $is_in_exception = false;
    protected $is_outputed = false;
    
    public static function ReCreateInstance()
    {
        $class = get_class(static::G());
        return static::G(new $class);
    }
    public function begin()
    {
        //if(ob)
        $this->is_running = true;
    }
    public function end()
    {
        //ob_end();
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
