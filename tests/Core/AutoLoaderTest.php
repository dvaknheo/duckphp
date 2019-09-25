<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\AutoLoader;

class AutoLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(AutoLoader::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(AutoLoader::class);
        $this->assertTrue(true);
        /*
        AutoLoader::G()->init($options=[], $context=null);
        AutoLoader::G()->run();
        AutoLoader::G()->_autoload($class);
        AutoLoader::G()->assignPathNamespace($path, $namespace=null);
        AutoLoader::G()->cacheClasses();
        AutoLoader::G()->cacheNamespacePath($path);
        AutoLoader::G()->cleanUp();
        //*/
    }
}
