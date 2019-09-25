<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\ExceptionManager;

class ExceptionManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ExceptionManager::class);
        
        $options=[
            
        ];
        ExceptionManager::G()->init($options);
        ExceptionManager::G()->init($options);
        ExceptionManager::G()->run();
        ExceptionManager::G()->run();
        ExceptionManager::G()->cleanUp();
        
        $default_exception_handler=null;
        $class="EX";
        $callback="callback";
        $classes=["ABC"];
        ExceptionManager::G(new ExceptionManager());
        
        ExceptionManager::G()->setDefaultExceptionHandler($default_exception_handler);
        ExceptionManager::G()->assignExceptionHandler($class, $callback=null);
        ExceptionManager::G()->setMultiExceptionHandler($classes, $callback);
        
        \MyCodeCoverage::G()->end(ExceptionManager::class);
        
        $this->assertTrue(true);
        /*
        
        ExceptionManager::G()->setMultiExceptionHandler(array $classes, $callback);
        ExceptionManager::G()->on_error_handler($errno, $errstr, $errfile, $errline);
        ExceptionManager::G()->checkAndRunErrorHandlers($ex, $inDefault);
        ExceptionManager::G()->on_exception($ex);

        //*/
    }
}
