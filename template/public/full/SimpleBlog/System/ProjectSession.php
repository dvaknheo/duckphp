<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use DuckPhp\Foundation\Session;

class ProjectSession extends Session
{
    public function __construct()
    {
        parent::__construct();
        $this->options['session_prefix'] = App::G()->getSessionPrefix();
        //$this->exception_class = SessionException::class;
    }
}
