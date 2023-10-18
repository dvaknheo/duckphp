<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Core\App;
use DuckPhp\SingletonEx\SingletonExTrait;

trait SimpleSessionTrait
{
    use SingletonExTrait;

    protected $session_started = false;
    
    protected function checkSessionStart()
    {
        if ($this->session_started) {
            return;
        }
        App::session_start();
        $this->options['session_prefix'] = App::Current()->options['session_prefix'] ?? '';
        $this->session_started = true;
    }
    protected function get($key, $default = null)
    {
        $this->checkSessionStart();
        return App::SessionGet($this->options['session_prefix'] . $key, $default);
    }
    protected function set($key, $value)
    {
        $this->checkSessionStart();
        return App::SessionSet($this->options['session_prefix'] . $key, $value);
    }
    protected function unset($key)
    {
        $this->checkSessionStart();
        return App::SessionUnset($this->options['session_prefix'] . $key);
    }
    /////////////////////////////////////
}
