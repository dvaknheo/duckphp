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
    public function __construct($overriding_class,$container_class,$strict=false)
    {
        $this->overriding_class = $overriding_class;
        $this->container_class = $container_class;
        $this->strict = $strict;
    }
    public function __call($method, $args)
    {
        $current = App::Phase();
        $flag = false;
        if(!$current){
            $flag = true;
        }else {
            if($this->strict){
                if($current === $this->container_class){
                    $flag = true;
                }
            }else{
                if(is_a($current,$this->container_class)){ //is_subclass_of ?
                    $flag = true;
                }
            }
        }
        if ($flag) {
            $callback = [($this->overriding_class)::G(),$method];
            return ($callback)(...$args);
        }
        App::Phase($this->container_class);
        $callback = [($this->overriding_class)::G(),$method];
        $ret = ($callback)(...$args);
        App::Phase($current);
        return $ret;
    }
}