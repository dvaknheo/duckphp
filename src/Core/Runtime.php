<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;

class Runtime extends ComponentBase
{
    public $options = [
        'use_output_buffer' => false,
        'path_runtime' => 'runtime',
    ];
    public $context_class;
    
    protected $is_running = false;
    protected $is_in_exception = false;
    protected $is_outputed = false;
    
    public $last_phase;
    protected $init_ob_level = 0;

    public function isRunning()
    {
        return $this->is_running;
    }
    public function isInException()
    {
        return $this->is_in_exception;
    }
    public function isOutputed()
    {
        return $this->is_outputed;
    }
    public function run()
    {
        if ($this->options['use_output_buffer']) {
            $this->init_ob_level = ob_get_level();
            ob_implicit_flush(0);
            ob_start();
        }
        $this->is_running = true;
    }
    public function clear()
    {
        if (!$this->is_running) {
            return false; 
        }
        if ($this->options['use_output_buffer']) {
            for ($i = ob_get_level();$i > $this->init_ob_level;$i--) {
                ob_end_flush();
            }
        }
        $this->is_in_exception = false;
        $this->is_running = false;
        $this->is_outputed = true;
    }
    public function onException($skip_exception_check)
    {
        if ($skip_exception_check) {
            $this->clear();
        }
        $this->is_in_exception = true;
    }
}
