<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\App;

class AppTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(App::class);
        
        $options=[];
        $options['skip_setting_file']=true;
        $options['error_exception']=null;
        $options['error_500']=null;
        $options['error_404']=null;
        $flag=\DNMVCS\DNMVCS::RunQuickly($options);
        
        \MyCodeCoverage::G()->end(App::class);
        $this->assertTrue(true);
        /*
        App::G()->RunQuickly(array $options=[], $after_init=null);
        App::G()->initOptions($options=[]);
        App::G()->checkOverride($options);
        App::G()->init(array $options=[], object $context=null);
        App::G()->onInit();
        App::G()->reloadFlags();
        App::G()->initExtentions(array $exts);
        App::G()->onRun();
        App::G()->run();
        App::G()->cleanUp();
        App::G()->cleanAll();
        App::G()->cleanClass($input_class);
        App::G()->addBeforeRunHandler($handler);
        App::G()->addBeforeShowHandler($handler);
        App::G()->extendComponents($class, $methods, $components);
        App::G()->getStaticComponentClasses();
        App::G()->getDynamicComponentClasses();
        App::G()->On404();
        App::G()->OnException($ex);
        App::G()->OnDevErrorHandler($errno, $errstr, $errfile, $errline);
        App::G()->_On404();
        App::G()->_OnException($ex);
        App::G()->_OnDevErrorHandler($errno, $errstr, $errfile, $errline);
        App::G()->header($output, bool $replace = true, int $http_response_code=0);
        App::G()->setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain  = '', bool $secure = false, bool $httponly = false);
        App::G()->exit_system($code=0);
        App::G()->set_exception_handler($exception_handler);
        App::G()->register_shutdown_function($callback, ...$args);
        App::G()->_header($output, bool $replace = true, int $http_response_code=0);
        App::G()->_setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain  = '', bool $secure = false, bool $httponly = false);
        App::G()->_exit_system($code=0);
        App::G()->_set_exception_handler($exception_handler);
        App::G()->_register_shutdown_function($callback, ...$args);
        App::G()->system_wrapper_replace(array $funcs=[]);
        App::G()->system_wrapper_get_providers();
        App::G()->ExitJson($ret);
        App::G()->ExitRedirect($url, $only_in_site=true);
        App::G()->ExitRouteTo($url);
        App::G()->Exit404();
        App::G()->_ExitJson($ret);
        App::G()->_ExitRedirect($url, $only_in_site=true);
        App::G()->Platform();
        App::G()->IsDebug();
        App::G()->IsInException();
        App::G()->Show($data=[], $view=null);
        App::G()->H($str);
        App::G()->_Show($data=[], $view=null);
        App::G()->_H($str);
        App::G()->DumpTrace();
        App::G()->Dump(...$args);
        App::G()->_DumpTrace();
        App::G()->_Dump(...$args);
        App::G()->IsRunning();
        App::G()->URL($url=null);
        App::G()->Parameters();
        App::G()->ShowBlock($view, $data=null);
        App::G()->Setting($key);
        App::G()->Config($key, $file_basename='config');
        App::G()->LoadConfig($file_basename);
        App::G()->assignPathNamespace($path, $namespace=null);
        App::G()->addRouteHook($hook, $prepend=false, $once=true);
        App::G()->stopRunDefaultHandler();
        App::G()->getRouteCallingMethod();
        App::G()->setRouteCallingMethod(string $method);
        App::G()->setViewWrapper($head_file=null, $foot_file=null);
        App::G()->assignViewData($key, $value=null);
        App::G()->assignExceptionHandler($classes, $callback=null);
        App::G()->setMultiExceptionHandler(array $classes, $callback);
        App::G()->setDefaultExceptionHandler($callback);
        App::G()->SG(object $replacement_object=null);
        App::G()->GLOBALS($k, $v=null);
        App::G()->STATICS($k, $v=null, $_level=1);
        App::G()->CLASS_STATICS($class_name, $var_name);
        App::G()->session_start(array $options=[]);
        App::G()->session_id($session_id=null);
        App::G()->session_destroy();
        App::G()->session_set_save_handler(\SessionHandlerInterface $handler);
        //*/
    }
}
