<?php
namespace tests\DuckPhp\SingletonEx;

use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\SingletonEx\SimpleReplacer;

class SimpleReplacerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        SingletonExObject3::G();

        \LibCoverage\LibCoverage::Begin(SimpleReplacer::class);
        $t=\LibCoverage\LibCoverage::G();
        SimpleReplacer::Replace();
        SimpleReplacer::Replace();
        \LibCoverage\LibCoverage::G($t);
        //SimpleReplacer::ReplaceSingletonEx();
        SingletonExObjectX::G(new SingletonExObjectX());
        SingletonExObjectX::G();
        SingletonExObject2::G();
        
        SimpleReplacer::$EnableCompactable=true;
        SingletonExObject3::G();
        SingletonExObject4::G();
        SingletonExObject5::G();

        \LibCoverage\LibCoverage::End();

    }
}
class SingletonExObjectX
{
    use \DuckPhp\SingletonEx\SingletonExTrait;
    
    public static function CreateObject($class, $object)
    {
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new $class));
        return $_instance[$class];
    }

}
class SingletonExObject2 extends SingletonExObjectX{}
class SingletonExObject3 extends SingletonExObjectX{}
class SingletonExObject4 extends SingletonExObjectX{}
class SingletonExObject5
{
     public static function G($object = null)
    {
        if (defined('__SINGLETONEX_REPALACER')) {
            $callback = __SINGLETONEX_REPALACER;
            return ($callback)(static::class, $object);
        }
    }
}