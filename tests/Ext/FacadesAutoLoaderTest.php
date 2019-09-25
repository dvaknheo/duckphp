<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\FacadesAutoLoader;

class FacadesAutoLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(FacadesAutoLoader::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(FacadesAutoLoader::class);
        $this->assertTrue(true);
        /*
        FacadesAutoLoader::G()->init($options=[], $context);
        FacadesAutoLoader::G()->_autoload($class);
        FacadesAutoLoader::G()->getFacadesCallback($class, $name);
        FacadesAutoLoader::G()->__callStatic($name, $arguments);
        //*/
    }
}
