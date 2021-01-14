<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Route;

class RouteHookManager extends ComponentBase
{
    public $options = [];
    protected $hook_list;
    
    public function attachPreRun()
    {
        $this->hook_list = & Route::G()->pre_run_hook_list;
        
        return $this;
    }
    public function attachPostRun()
    {
        $this->hook_list = & Route::G()->post_run_hook_list;
        return $this;
    }
    public function detach()
    {
        unset($this->hook_list);
    }
    public function getHookList()
    {
        return $this->hook_list;
    }
    public function setHookList($hook_list)
    {
        $this->hook_list = $hook_list;
    }
    public function moveBefore($new, $old)
    {
        $this->removeAll($new);
        $this->insertBefore($new, $old);
        return $this;
    }
    public function insertBefore($new, $old)
    {
        $ret = [];
        foreach ($this->hook_list as $hook) {
            if ($hook === $old) {
                $ret[] = $new;
            }
            $ret[] = $hook;
        }
        $this->hook_list = $ret;
        return $this;
    }
    public function removeAll($name)
    {
        $ret = [];
        foreach ($this->hook_list as $hook) {
            if ($hook === $name) {
                continue;
            }
            $ret[] = $hook;
        }
        $this->hook_list = $ret;
        return $this;
    }
    public function append($name)
    {
        $this->hook_list[] = $name;
    }
    public function dump()
    {
        $ret = Route::G()->dumpAllRouteHooksAsString();
        return $ret;
    }
}
