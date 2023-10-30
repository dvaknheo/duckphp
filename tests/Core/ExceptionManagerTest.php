<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\ExceptionManager;

class ExceptionManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ExceptionManager::class);

         $exception_options=[
            'default_exception_handler'=>[ExceptionManagerObject::class,'CallException'],
            'dev_error_handler'=>[ExceptionManagerObject::class,'OnDevErrorHandler'],
            //'system_exception_handler'=>[ExceptionManager::_(),'on_exception'],
        ];
        ExceptionManager::_()->init($exception_options);
        ExceptionManager::_()->init($exception_options);
        ExceptionManager::_()->run();
        ExceptionManager::_()->run();
        
        trigger_error("Just use e11",E_USER_NOTICE);
        error_reporting(error_reporting() & ~E_USER_NOTICE);
        trigger_error("Just use error2222222222",E_USER_NOTICE);
        ExceptionManager::_()->on_error_handler(E_DEPRECATED, "Just use e11",__FILE__,__LINE__);
        ExceptionManager::_()->on_error_handler(E_USER_DEPRECATED, "Just use e11",__FILE__,__LINE__);

        try{
        ExceptionManager::_()->on_error_handler(E_USER_ERROR, "Just use e11",__FILE__,__LINE__);
        }catch(\ErrorException $ex){
        }
        $ex=new ExceptionManagerException("ABC",123);
        $ex2=new \Exception("ABCss",123);
        
        ExceptionManager::_()->assignExceptionHandler(ExceptionManagerException::class, function($ex){
            var_dump("OK");
        });
        ExceptionManager::CallException($ex);
        ExceptionManager::CallException($ex2);
        
        ExceptionManager::_()->clear();
        
        $default_exception_handler=[ExceptionManager::_(),'onException'];
        $class="EX";
        $callback="callback";
        $classes=["ABC"];
        ExceptionManager::_(new ExceptionManager());
        
        ExceptionManager::_()->setDefaultExceptionHandler($default_exception_handler);
        ExceptionManager::_()->assignExceptionHandler($class, $callback=null);
        ExceptionManager::_()->setMultiExceptionHandler($classes, $callback);
        
        
        $exception_options=[
            'system_exception_handler'=>[new ExceptionManagerObject(),'set_exception_handler'],
        ];
        ExceptionManager::_(new ExceptionManager())->init($exception_options)->run();
        
        ExceptionManager::_()->clear();
        
        ExceptionManager::_()->isInited();


        ExceptionManager::_(new ExceptionManager())->reset();
        $t=\LibCoverage\LibCoverage::G();
        define('__SINGLETONEX_REPALACER',ExceptionAutoLoaderObject::class.'::CreateObject');
        \LibCoverage\LibCoverage::G($t);
        ExceptionManager::_();
        
        \LibCoverage\LibCoverage::End();
        /*
        
        ExceptionManager::_()->setMultiExceptionHandler(array $classes, $callback);
        ExceptionManager::_()->on_error_handler($errno, $errstr, $errfile, $errline);
        ExceptionManager::_()->checkAndRunErrorHandlers($ex, $inDefault);
        ExceptionManager::_()->on_exception($ex);

        //*/

    }
}
class ExceptionManagerException extends \Exception
{

}
class ExceptionManagerObject
{
    public static function set_exception_handler( $exception_handler)
    {

    }
    static function CallException(\Throwable $ex)
    {
        //if( ExceptionManager::_()->
        echo "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
        var_dump(get_class($ex),DATE(DATE_ATOM));
    }
    static function OnDevErrorHandler($errno, $errstr, $errfile, $errline)
    {
        echo (sprintf("ERROR:%X;",$errno).'~'.$errstr.'~'.$errfile.'~'.$errline."\n");
    }
}
class ExceptionAutoLoaderObject
{    
    public static function CreateObject($class, $object)
    {
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new $class));
        return $_instance[$class];
    }

}