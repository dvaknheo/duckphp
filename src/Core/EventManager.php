<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;

class EventManager extends ComponentBase
{
    protected $events = [];
    public static function OnEvent($event, $callback)
    {
        return static::_()->on($event, $callback);
    }
    public static function FireEvent($event, ...$args)
    {
        return static::_()->fire($event, ...$args);
    }
    public static function AllEvents()
    {
        return static::_()->all();
    }
    public static function RemoveEvent($event, $callback = null)
    {
        return static::_()->remove($event, $callback);
    }
    public function on($event, $callback)
    {
        $event = $this->eventName($event);
        if (isset($this->events[$event]) && in_array($callback, $this->events[$event])) {
            return;
        }
        $this->events[$event][] = $callback;
    }
    public function fire($event, ...$args)
    {
        $event = $this->eventName($event);
        if (!isset($this->events[$event])) {
            return;
        }
        $a = $this->events[$event];
        foreach ($a as $v) {
            ($v)(...$args);
        }
    }
    public function all()
    {
        return $this->events;
    }
    public function remove($event, $callback = null)
    {
        $event = $this->eventName($event);
        if (!isset($callback)) {
            unset($this->events[$event]);
        }
        if (!isset($this->events[$event])) {
            return;
        }
        $this->events[$event] = array_filter($this->events[$event], function ($v) use ($callback) {
            return $v != $callback;
        });
    }
    protected function eventName($event)
    {
        if (is_array($event)) {
            $event = implode('::', $event);
        }
        return $event;
    }
}
