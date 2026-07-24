<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class GlobalEvent extends ComponentBase
{
    protected $events = [];
    public function on($event, $callback)
    {
        return $this->globalOn($event, App::Phase(), $callback);
    }
    public function globalOn($event, ?string $phase = null, $callback)
    {
        $pair = [$phase, $callback];
        if (isset($this->events[$event]) && in_array($pair, $this->events[$event])) {
            return;
        }
        $this->events[$event][] = $pair;
    }
    public function fire($event, ...$args)
    {
        if (!isset($this->events[$event])) {
            return;
        }
        $a = $this->events[$event];
        foreach ($a as $v) {
            [$phase, $callback] = $v;
            $old_phase = App::Phase($phase);
            ($callback)(...$args);
            App::Phase($old_phase);
        }
    }
    public function all()
    {
        return $this->events;
    }
    public function remove($event, ?string $phase = null, $callback = null)
    {
        if (!isset($this->events[$event])) {
            return;
        }
        if (empty($phase) && !isset($callback)) {
            unset($this->events[$event]);
            return;
        }
        $this->events[$event] = array_filter($this->events[$event], function ($v) use ($phase, $callback) {
            [$calling_phase, $calling_callback] = $v;
            return ($calling_phase != $phase) && ($calling_callback != $callback);
        });
    }
}
