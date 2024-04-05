<?php
namespace ProjectNameTemplate\Controller;

use DuckPhp\DuckPhp;
use DuckPhp\Component\CommandTrait;
use DuckPhp\Foundation\SimpleControllerTrait;

class Commands
{
    use SimpleControllerTrait;
    use CommandTrait;
    /**
     * console command sample
     */
    public function command_hello()
    {
        echo "hello ". static::class ."\n";
    }
}