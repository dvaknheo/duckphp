<?php 
namespace tests\DNMVCS\Core;
use DNMVCS\Core\ExceptionManager;

class ExceptionManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ExceptionManager::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(ExceptionManager::class);
        $this->assertTrue(true);
        /*
        ExceptionManager::G()->setDefaultExceptionHandler($default_exception_handler);
        ExceptionManager::G()->assignExceptionHandler($class, $callback=null);
        ExceptionManager::G()->setMultiExceptionHandler(array $classes, $callback);
        ExceptionManager::G()->on_error_handler($errno, $errstr, $errfile, $errline);
        ExceptionManager::G()->checkAndRunErrorHandlers($ex, $inDefault);
        ExceptionManager::G()->on_exception($ex);
        ExceptionManager::G()->init($options=[], $context=null);
        ExceptionManager::G()->run();
        ExceptionManager::G()->cleanUp();
        //*/
    }
}
