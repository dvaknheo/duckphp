<?php 
namespace tests\DNMVCS\Core;
use DNMVCS\Core\Configer;

class ConfigerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Configer::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(Configer::class);
        $this->assertTrue(true);
        /*
        Configer::G()->init($options=[], $context=null);
        Configer::G()->_Setting($key);
        Configer::G()->_Config($key, $file_basename='config');
        Configer::G()->_LoadConfig($file_basename='config');
        Configer::G()->loadFile($file);
        //*/
    }
}
