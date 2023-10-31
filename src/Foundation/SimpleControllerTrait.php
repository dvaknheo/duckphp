<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Component\CallInPhaseTrait;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\Route;

trait SimpleControllerTrait
{
    use CallInPhaseTrait;
    
    public static function _($object = null)
    {
        $route = Route::_();
        $postfix = $route->options['controller_class_postfix'];
        $class_base = $route->options['controller_class_base'];
        
        /*
        postfix_set,postfix_match  base_set ,base_match , result
        Y Y Y Y => Y
        Y Y Y N => N
        Y Y N * => Y
        Y N * * => N
        N * Y Y => Y
        N * Y N => N
        N * N * => Y
        ------------
        Y Y Y Y => Y
        Y Y Y N => N
        Y Y N Y => Y
        Y Y N N => Y
        Y N Y Y => N
        Y N Y N => N
        Y N N Y => N
        Y N N N => N
        N Y Y Y => Y
        N N Y Y => Y
        N Y Y N => N
        N N Y N => N
        N Y N Y => Y
        N Y N N => Y
        N N N Y => Y
        N N N N => Y
        //*/
        $is_controller = false;
        
        if ($postfix) {
            if (substr(static::class, -strlen($postfix)) === $postfix) {
                if ($class_base) {
                    if (\is_subclass_of(static::class, $class_base)) {
                        $is_controller = true;
                    } else {
                        $is_controller = false;
                    }
                } else {
                    $is_controller = true;
                }
            } else {
                $is_controller = false;
            }
        } else {
            if ($class_base) {
                if (\is_subclass_of(static::class, $class_base)) {
                    $is_controller = true;
                } else {
                    $is_controller = false;
                }
            } else {
                $is_controller = true;
            }
        }
        if ($is_controller) {
            $class = $object ?  get_class($object) :static::class ;
            if ($class !== static::class) {
                $route->options['controller_class_map'][static::class] = $class;
            }
            $object = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
            return $object;
        }
        $ret = PhaseContainer::GetObject(static::class, $object);
        return $ret;
    }
}
