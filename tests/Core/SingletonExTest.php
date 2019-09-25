<?php 
namespace tests\DNMVCS\Core;
use DNMVCS\Core\SingletonEx;

class SingletonExTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SingletonEx::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(SingletonEx::class);
        $this->assertTrue(true);
        /*
        SingletonEx::G()->G($object=null);
        //*/
    }
}
