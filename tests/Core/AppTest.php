<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\App;

class AppTest extends \PHPUnit\Framework\TestCase
{
    static $x;
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(App::class);
        
        $options=[];
        $options['skip_setting_file']=true;
        $options['error_exception']=null;
        $options['error_500']=null;
        $options['error_404']=null;
        $flag=App::RunQuickly($options);
        App::G()->cleanUp();
        App::G()->cleanAll();
        App::G(new App());
        $this->do3();
        $this->do2();
        
        
        \MyCodeCoverage::G()->end(App::class);
        $this->assertTrue(true);
    }
    public function do3()
    {
        App::OnException(new \Exception("EX",-1));
        //App::G()->extendComponents();
        App::G()->getStaticComponentClasses();
        App::G()->getDynamicComponentClasses();
    }
    public function do2()
    {
        $path_base=realpath(__DIR__.'/../');
        $path_config=$path_base.'/data_for_tests/Core/Helper/ControllerHelper/';
        $options=[
            'skip_setting_file'=>true,
            'path_config'=>$path_config,
        ];
        \DNMVCS\Core\Configer::G()->init($options);
        $key='key';
        $file_basename='config';
        
        App::Setting($key);
        App::Config($key, $file_basename);
        App::LoadConfig($file_basename);
        
        $str='<>';
        $url="";
        $method="method";
        App::H($str);
        App::URL($url=null);
        App::Parameters();
        App::getRouteCallingMethod();
        App::setRouteCallingMethod($method);
        //*/
        //*
        $path_base=realpath(__DIR__.'/../');
        $path_view=$path_base.'/data_for_tests/Core/Helper/ControllerHelper/';
        $options=[
            'path_view'=>$path_view,
        ];
        \DNMVCS\Core\View::G()->init($options);
        App::Show(['A'=>'b'],"view");
        App::ShowBlock("view",['A'=>'b']);
        
        $key="key";
        App::setViewWrapper($head_file=null, $foot_file=null);
        App::assignViewData($key, $value=null);
        
        //*/
        $url="/abc";
        $path_info="aa/bb";
        $ret=["ret"=>'OK'];
        
        $output="";
        
        \DNMVCS\Core\App::G()->exit_handler=function($code){
            var_dump(DATE(DATE_ATOM));
        };
        //*
        App::ExitRedirect($url, $only_in_site=true);
        App::ExitRouteTo($url);
        App::Exit404();
        App::ExitJson($ret);
        //*/
        
        App::header($output,$replace = true, $http_response_code=0);
        App::exit_system($code=0);
        
        
        $classes=[];
        $callback=function($code){
            var_dump(DATE(DATE_ATOM));
        };
        App::assignExceptionHandler($classes, $callback);
        App::setMultiExceptionHandler($classes, $callback);
        App::setDefaultExceptionHandler($callback);
        
        $k="k";$v="v";
        $class_name=static::class;
        $var_name="x";
        App::SG();
        App::GLOBALS($k, $v=null);
        App::STATICS($k, $v=null);
        App::CLASS_STATICS($class_name, $var_name);        
        
        App::session_start($options=[]);
        App::session_id(null);
        App::session_destroy();
        $handler=new FakeSessionHandler();
        App::session_set_save_handler( $handler);
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