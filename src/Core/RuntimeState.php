<?php
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class RuntimeState
{
    use SingletonEx;
    
    protected $is_running=false;
    public $is_before_show_done=false;
    protected $error_reporting_old;
    
    public function isRunning()
    {
        return $this->is_running;
    }
    public static function ReCreateInstance()
    {
        $class=get_class(static::G());
        static::G(new $class);
    }
    public function begin()
    {
        $this->is_running=true;
        $this->error_reporting_old=error_reporting();
    }
    public function end()
    {
        error_reporting($this->error_reporting_old);
        $this->is_running=false;
    }
    public function skipNoticeError()
    {
        $this->error_reporting_old =error_reporting();
        error_reporting($this->error_reporting_old & ~E_NOTICE);
    }
}
