<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System;


use DuckPhp\Foundation\SessionManagerBase;
use DuckPhp\Foundation\ThrowOnableTrait;

class ProjectSession extends SessionManagerBase
{
    use ThrowOnableTrait;
    public function __construct()
    {
        parent::__construct();
        $this->options['session_prefix'] = App::G()->getSessionPrefix();
        $this->exception_class = SessionException::class;
    }
}
