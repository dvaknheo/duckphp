<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class SessionManagerBase extends ComponentBase
{
    public $options = [
        'session_prefix' => '',
    ];
    protected $session_started = false;
    
    protected function checkSessionStart()
    {
        if ($this->session_started) {
            return;
        }
        App::session_start();
        $this->session_started = true;
    }
    protected function get($key, $default = null)
    {
        $this->checkSessionStart();
        App::SessionGet($this->options['session_prefix'] . $key, $default);
    }
    protected function set($key, $value)
    {
        $this->checkSessionStart();
        App::SessionSet($this->options['session_prefix'] . $key, $value);
    }
    protected function unset($key)
    {
        $this->checkSessionStart();
        App::SessionUnset($this->options['session_prefix'] . $key);
    }
    /////////////////////////////////////
}
