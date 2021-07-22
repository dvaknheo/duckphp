<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace LazyToChange\System;

use DuckPhp\Foundation\ThrowOnableTrait;
use DuckPhp\Foundation\SessionManagerBase;

class ProjectSession extends SessionManagerBase
{
    use ThrowOnableTrait;
    
    public function __construct()
    {
        parent::__construct();
    }
}
