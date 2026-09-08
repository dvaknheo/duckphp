<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Foundation;

use DuckPhp\Core\App;
use DuckPhp\Core\SingletonExTrait;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\SystemWrapper;

trait SessionTrait
{
    use SingletonExTrait;

    protected $session_started = false;
    protected $session_prefix = '';
    protected function checkSessionStart(): void
    {
        if ($this->session_started) {
            return;
        }
        SystemWrapper::_()->_session_start();
        $this->session_started = true;
        $this->session_prefix = (string)(App::_()->options['session_prefix'] ?? '');
    }
    protected function get(string $key, $default = null)
    {
        $this->checkSessionStart();
        return SuperGlobal::_()->_SessionGet($this->session_prefix . $key, $default);
    }
    protected function set(string $key, $value)
    {
        $this->checkSessionStart();
        return SuperGlobal::_()->_SessionSet($this->session_prefix . $key, $value);
    }
    protected function unset(string $key)
    {
        $this->checkSessionStart();
        return SuperGlobal::_()->_SessionUnset($this->session_prefix . $key);
    }
    /////////////////////////////////////
}
