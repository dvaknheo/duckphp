<?php
namespace ProjectNameTemplate\Controller;

use DuckPhp\DuckPhp;
use DuckPhp\Foundation\CommonCommandTrait;
use DuckPhp\Foundation\ControllerTrait;

class Commands
{
    use ControllerTrait;
    use CommonCommandTrait;
    /**
     * console command sample
     */
    public function command_hello()
    {
        echo "hello ". static::class ."\n";
    }
    /**
     * console command sample
     */
    public function command_t()
    {
        var_dump(\DuckPhp\Core\Console::_()->getCliParameters());
    }
}