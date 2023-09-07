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
    protected $strict = false;
    public function __construct($container_class, $overriding_class, $strict = false)
    {
        $this->overriding_class = $overriding_class;
        $this->container_class = $container_class;
        $this->strict = $strict;
    }
    public static function CreatePhaseProxy($container_class, $overriding_class, $strict = false)
    {
        return new static($container_class, $overriding_class, $strict);
    }
    public function __call($method, $args)
    {
        $current = App::Phase();
        $flag = false;
        if (!$current) {
            $flag = true;
        } else {
            if ($this->strict) {
                if ($current === $this->container_class) {
                    $flag = true;
                }
            } else {
                if (is_a($current, $this->container_class)) { //is_subclass_of ?
                    $flag = true;
                }
            }
        }
        if ($flag) {
            $callback = [($this->overriding_class)::G(),$method];
            return ($callback)(...$args); /** @phpstan-ignore-line */
        }
        App::Phase($this->container_class);
        $callback = [($this->overriding_class)::G(),$method];
        $ret = ($callback)(...$args); /** @phpstan-ignore-line */
        App::Phase($current);
        return $ret;
    }
}
