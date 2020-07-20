<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class EventManager extends ComponentBase
{
    protected $events = [];
    public static function FireEvent($event, ...$args)
    {
        return static::G()->fire($event, ...$args);
    }
    public static function OnEvent($event, $callback)
    {
        return static::G()->fire($event, $callback);
    }
    public function on($event, $callback)
    {
        $this->events[$event] = $callback;
    }
    public function fire($event, ...$args)
    {
        if (!isset($this->events[$event])) {
            return;
        }
        return ($this->events)(...$args);
    }
}
