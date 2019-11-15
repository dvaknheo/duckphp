<?php
namespace tests\DNMVCS\Core\Helper;

use DNMVCS\Core\Helper\ControllerHelper;

class ControllerHelperTest extends \PHPUnit\Framework\TestCase
{
    static $x;
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ControllerHelper::class);
        
        //code here
        //*
        $path_base=realpath(__DIR__.'/../../');
        $path_config=$path_base.'/data_for_tests/Core/Helper/ControllerHelper/';
        $options=[
            'skip_setting_file'=>true,
            'path_config'=>$path_config,
        ];
        \DNMVCS\Core\Configer::G()->init($options);
        $key='key';
        $file_basename='config';
        
        ControllerHelper::Setting($key);
        ControllerHelper::Config($key, $file_basename);
        ControllerHelper::LoadConfig($file_basename);
        
        $str='<>';
        $url="";
        $method="method";
        ControllerHelper::H($str);
        ControllerHelper::URL($url=null);
        ControllerHelper::Parameters();
        ControllerHelper::getRouteCallingMethod();
        ControllerHelper::setRouteCallingMethod($method);
        
        
        

        //*/
        //*
        $path_base=realpath(__DIR__.'/../../');
        $path_view=$path_base.'/data_for_tests/Core/Helper/ControllerHelper/';
        $options=[
            'path_view'=>$path_view,
        ];
        \DNMVCS\Core\View::G()->init($options);
        ControllerHelper::Show(['A'=>'b'],"view");
        ControllerHelper::ShowBlock("view",['A'=>'b']);
        
        $key="key";
        ControllerHelper::setViewWrapper($head_file=null, $foot_file=null);
        ControllerHelper::assignViewData($key, $value=null);
        
        //*/
        $url="/abc";
        $path_info="aa/bb";
        $ret=["ret"=>'OK'];
        
        $output="";

        \DNMVCS\Core\App::G()->exit_handler=function($code){
            var_dump(DATE(DATE_ATOM));
        };
        /*
        ControllerHelper::ExitRedirect($url, $only_in_site=true);
        ControllerHelper::ExitRouteTo($url);
        ControllerHelper::Exit404();
        ControllerHelper::ExitJson($ret);
        //*/
        
        //ControllerHelper::header($output,$replace = true, $http_response_code=0);
        //ControllerHelper::exit_system($code=0);
        
        
        $classes=[];
        $callback=function($code){
            var_dump(DATE(DATE_ATOM));
        };
        ControllerHelper::assignExceptionHandler($classes, $callback);
        ControllerHelper::setMultiExceptionHandler($classes, $callback);
        ControllerHelper::setDefaultExceptionHandler($callback);
        
        $k="k";$v="v";
        $class_name=static::class;
        $var_name="x";
        ControllerHelper::SG();
        ControllerHelper::GLOBALS($k, $v=null);
        ControllerHelper::STATICS($k, $v=null);
        ControllerHelper::CLASS_STATICS($class_name, $var_name);        
        
        ControllerHelper::session_start($options=[]);
        ControllerHelper::session_id(null);
        ControllerHelper::session_destroy();
        $handler=new FakeSessionHandler();
        ControllerHelper::session_set_save_handler( $handler);
        
        \MyCodeCoverage::G()->end(ControllerHelper::class);
        $this->assertTrue(true);


        //*/
    }
}
class FakeSessionHandler implements \SessionHandlerInterface
{
    public function open($savePath, $sessionName)
    {
    }
    public function close()
    {
    }
    public function read($id)
    {
    }
    public function write($id, $data)
    {
    }
    public function destroy($id)
    {
        return true;
    }
    public function gc($maxlifetime)
    {
        return true;
    }
}