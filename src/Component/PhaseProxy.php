<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;

class PhaseProxy
{
    protected $container_class;
    protected $overriding_class;
    public function __construct($container_class, $overriding_class)
    {
        $this->overriding_class = $overriding_class;
        $this->container_class = $container_class;
    }
    public static function CreatePhaseProxy($container_class, $overriding_class)
    {
        return new static($container_class, $overriding_class);
    }
    protected function createObjectForPhaseProxy()
    {
        return ($this->overriding_class)::G();
    }

    public function __call($method, $args)
    {
        $phase = App::Phase();
        App::Phase($this->container_class);
        
        $object = $this->createObjectForPhaseProxy();

        $callback = [$object,$method];
        $ret = ($callback)(...$args); /** @phpstan-ignore-line */
        // if exception ?
        App::Phase($phase);
        return $ret;
    }
}
