<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Core\App;
use DuckPhp\Core\SingletonTrait;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\SystemWrapper;

trait SimpleSessionTrait
{
    use SingletonTrait;

    protected $session_started = false;
    protected $options =[];
    protected function checkSessionStart()
    {
        if ($this->session_started) {
            return;
        }
        SystemWrapper::_()->_session_start();
        $this->options['session_prefix'] = App::Current()->options['session_prefix'] ?? '';
        $this->session_started = true;
    }
    protected function get($key, $default = null)
    {
        $this->checkSessionStart();
        return SuperGlobal::_()->_SessionGet(($this->options['session_prefix'] ?? '') . $key, $default);
    }
    protected function set($key, $value)
    {
        $this->checkSessionStart();
        return SuperGlobal::_()->_SessionSet(($this->options['session_prefix'] ?? '') . $key, $value);
    }
    protected function unset($key)
    {
        $this->checkSessionStart();
        return SuperGlobal::_()->_SessionUnset(($this->options['session_prefix'] ?? '') . $key);
    }
    /////////////////////////////////////
}
