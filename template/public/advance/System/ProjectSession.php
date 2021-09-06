<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace LazyToChange\System;

use DuckPhp\Foundation\ThrowOnableTrait;
use DuckPhp\Foundation\Session;

class ProjectSession extends Session
{
    use ThrowOnableTrait;
    
    public function __construct()
    {
        parent::__construct();
    }
}
