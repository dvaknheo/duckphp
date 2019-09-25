<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class SingletonExTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SingletonEx::class);
        
        SingletonExObject::G();
        //define('DNMVCS_SINGLETONEX_REPALACER',SingletonExObject::class.'::CreateObject');
        //SingletonExObject::G();
        
        \MyCodeCoverage::G()->end();
        $this->assertTrue(true);
        /*
        SingletonEx::G()->G($object=null);
        //*/
    }
}
class SingletonExObject
{
    use SingletonEx;
    
    public static function CreateObject($class, $object)
    {
        return new $class;
    }
}
