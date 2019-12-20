<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\Lazybones;

class LazybonesTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Lazybones::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(Lazybones::class);
        $this->assertTrue(true);
        /*
        Lazybones::G()->__construct();
        Lazybones::G()->init(array $options, object $context = null);
        Lazybones::G()->MapToService($serviceClass, $input);
        Lazybones::G()->explodeService($object, $namespace = null);
        Lazybones::G()->runRoute();
        Lazybones::G()->getCallback($full_class, $method);
        Lazybones::G()->getRouteDispatchInfo($blocks, $method);
        Lazybones::G()->getFullClassByNoNameSpace($path_class, $confirm = false);
        Lazybones::G()->checkLoadClass($path_class);
        Lazybones::G()->includeControllerFile($file);
        Lazybones::G()->getClassMethodAndParameters($blocks, $method);
        Lazybones::G()->getControllerByFiles();
        //*/
    }
}
