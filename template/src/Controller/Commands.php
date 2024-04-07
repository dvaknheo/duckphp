<?php
namespace ProjectNameTemplate\Controller;

use DuckPhp\DuckPhp;
use DuckPhp\Foundation\CommonCommandTrait;
use DuckPhp\Foundation\SimpleControllerTrait;

class Commands
{
    use SimpleControllerTrait;
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