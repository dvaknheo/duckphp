<?php 
namespace tests\DNMVCS\Core\Helper;
use DNMVCS\Core\Helper\ControllerHelper;

class ControllerHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ControllerHelper::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(ControllerHelper::class);
        $this->assertTrue(true);
        /*
        ControllerHelper::G()->Setting($key);
        ControllerHelper::G()->Config($key, $file_basename='config');
        ControllerHelper::G()->LoadConfig($file_basename);
        ControllerHelper::G()->H($str);
        ControllerHelper::G()->URL($url=null);
        ControllerHelper::G()->Parameters();
        ControllerHelper::G()->getRouteCallingMethod();
        ControllerHelper::G()->setRouteCallingMethod($method);
        ControllerHelper::G()->Show($data=[], $view=null);
        ControllerHelper::G()->ShowBlock($view, $data=null);
        ControllerHelper::G()->setViewWrapper($head_file=null, $foot_file=null);
        ControllerHelper::G()->assignViewData($key, $value=null);
        ControllerHelper::G()->ExitRedirect($url, $only_in_site=true);
        ControllerHelper::G()->ExitRouteTo($url);
        ControllerHelper::G()->Exit404();
        ControllerHelper::G()->ExitJson($ret);
        ControllerHelper::G()->header($output, bool $replace = true, int $http_response_code=0);
        ControllerHelper::G()->exit_system($code=0);
        ControllerHelper::G()->assignExceptionHandler($classes, $callback=null);
        ControllerHelper::G()->setMultiExceptionHandler(array $classes, $callback);
        ControllerHelper::G()->setDefaultExceptionHandler($callback);
        ControllerHelper::G()->SG();
        ControllerHelper::G()->GLOBALS($k, $v=null);
        ControllerHelper::G()->STATICS($k, $v=null);
        ControllerHelper::G()->CLASS_STATICS($class_name, $var_name);
        ControllerHelper::G()->session_start(array $options=[]);
        ControllerHelper::G()->session_id($session_id=null);
        ControllerHelper::G()->session_destroy();
        ControllerHelper::G()->session_set_save_handler(\SessionHandlerInterface $handler);
        //*/
    }
}
