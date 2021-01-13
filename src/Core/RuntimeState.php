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
        'use_output_buffer' => false,
    ];
    public $context_class;
    protected $is_running = false;
    protected $is_in_exception = false;
    protected $is_outputed = false;
    protected $init_ob_level = 0;
    
    public function run()
    {
        if ($this->options['use_output_buffer']) {
            $this->init_ob_level = ob_get_level();
            ob_implicit_flush(0);
            ob_start();
        }
        $this->is_running = true;
    }
    public function reset()
    {
        $self = self::G();
        $options = $self->options;
        $context = $self->context_class ? $self->context_class::G() : null;
        self::G(new static())->init($options, $context)->run();
    }
    public function clear()
    {
        if ($this->options['use_output_buffer']) {
            for ($i = ob_get_level();$i > $this->init_ob_level;$i--) {
                ob_end_flush();
            }
        }
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
