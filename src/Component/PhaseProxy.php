<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;

class PhaseProxy
{
    public $container_class;
    protected $overriding;
    public function __construct($container_class, $overriding)
    {
        $this->overriding = $overriding;
        $this->container_class = $container_class;
    }
    public static function CreatePhaseProxy($container_class, $overriding)
    {
        $container_class = $container_class ?? App::Phase();
        return new static($container_class, $overriding);
    }
    protected function getObjectForPhaseProxy()
    {
        return is_object($this->overriding) ? $this->overriding : $this->overriding::_();
    }

    public function __call($method, $args)
    {
        $phase = App::Phase($this->container_class);
        
        $object = $this->getObjectForPhaseProxy();

        $callback = [$object,$method];
        $ret = ($callback)(...$args); /** @phpstan-ignore-line */
        App::Phase($phase);
        return $ret;
    }
    public function self()
    {
        return $this->getObjectForPhaseProxy();
    }
}
