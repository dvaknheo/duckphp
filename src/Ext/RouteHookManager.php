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
    
    public function attachPreRun(): self
    {
        $this->hook_list = & Route::_()->pre_run_hook_list;
        
        return $this;
    }
    public function attachPostRun(): self
    {
        $this->hook_list = & Route::_()->post_run_hook_list;
        return $this;
    }
    public function detach(): void
    {
        unset($this->hook_list);
    }
    public function getHookList(): array
    {
        return $this->hook_list;
    }
    public function setHookList(array $hook_list): void
    {
        $this->hook_list = $hook_list;
    }
    public function moveBefore($new, $old): self
    {
        $this->removeAll($new);
        $this->insertBefore($new, $old);
        return $this;
    }
    public function insertBefore($new, $old): self
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
    public function removeAll($name): self
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
    public function append($name): void
    {
        $this->hook_list[] = $name;
    }
    public function dump(): string
    {
        $ret = Route::_()->dumpAllRouteHooksAsString();
        return $ret;
    }
}
