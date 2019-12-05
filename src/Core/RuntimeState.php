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
    public $is_before_show_done = false;
    public $is_in_exception = false;
    public $running_exception = false;
    
    protected $error_reporting_old;
    
    public function __construct()
    {
    }
    public function isRunning()
    {
        return $this->is_running;
    }
    public static function ReCreateInstance()
    {
        $class = get_class(static::G());
        return static::G(new $class);
    }
    public function begin()
    {
        $this->is_running = true;
        $this->error_reporting_old = error_reporting();
    }
    public function end()
    {
        error_reporting($this->error_reporting_old);
        $this->is_in_exception = false;
        $this->is_running = false;
    }
    public function skipNoticeError()
    {
        $this->error_reporting_old = error_reporting();
        error_reporting($this->error_reporting_old & ~E_NOTICE);
    }
}
