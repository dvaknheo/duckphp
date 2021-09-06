<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System;


use DuckPhp\Foundation\Session;
use DuckPhp\Foundation\ThrowOnableTrait;
use SimpleAuth\System\App;

class ProjectSession extends Session
{
    use ThrowOnableTrait;
    public function __construct()
    {
        parent::__construct();
        $this->options['session_prefix'] = App::G()->getSessionPrefix();
        $this->exception_class = SessionException::class;
    }
}
