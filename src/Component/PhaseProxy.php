<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;

class PhaseProxy
{
    protected $phase;
    protected $overriding;
    public function __construct($phase, $overriding)
    {
        $this->overriding = $overriding;
        $this->phase = $phase;
    }
    public static function CreatePhaseProxy($phase, $overriding)
    {
        $phase = $phase ?? App::Phase();
        return new static($phase, $overriding);
    }
    protected function getObjectForPhaseProxy(): object
    {
        $this->overriding = is_object($this->overriding) ? $this->overriding : new $this->overriding;
        return $this->overriding;
    }

    public function __call($method, $args)
    {
        $phase = App::Phase($this->phase);
        
        $object = $this->getObjectForPhaseProxy();

        $callback = [$object,$method];
        $ret = ($callback)(...$args); /** @phpstan-ignore-line */
        App::Phase($phase);
        return $ret;
    }
    public function self(): object
    {
        return $this->getObjectForPhaseProxy();
    }
    public function phase($new = null)
    {
        $this->phase = $neww ?? $this->phase;
        return $this->phase;
    }
}
