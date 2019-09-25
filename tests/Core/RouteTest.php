<?php 
namespace tests\DNMVCS\Core;
use DNMVCS\Core\Route;

class RouteTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Route::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(Route::class);
        $this->assertTrue(true);
        /*
        Route::G()->RunQuickly(array $options=[], $after_init=null);
        Route::G()->URL($url=null);
        Route::G()->Parameters();
        Route::G()->_URL($url=null);
        Route::G()->_Parameters();
        Route::G()->defaultURLHandler($url=null);
        Route::G()->init($options=[], $context=null);
        Route::G()->bindServerData($server);
        Route::G()->setURLHandler($callback);
        Route::G()->getURLHandler();
        Route::G()->addRouteHook($hook, $prepend=false, $once=true);
        Route::G()->beforeRun();
        Route::G()->run();
        Route::G()->prepend(object $object);
        Route::G()->append(object $object);
        Route::G()->stopRunDefaultHandler();
        Route::G()->getFullClassByAutoLoad($path_class);
        Route::G()->defaultRouteHandler();
        Route::G()->getCallback($full_class, $method);
        Route::G()->createControllerObject($full_class);
        Route::G()->getMethodToCall($obj, $method);
        Route::G()->getRouteCallingPath();
        Route::G()->getRouteCallingClass();
        Route::G()->getRouteCallingMethod();
        Route::G()->setRouteCallingMethod($calling_method);
        //*/
    }
}
