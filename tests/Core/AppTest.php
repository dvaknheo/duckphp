<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\App;
use DNMVCS\Core\Configer;
use DNMVCS\Core\View;
use DNMVCS\Core\SingletonEx;

class AppTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(App::class);
        
        $options=[];
        $options['path']=__DIR__;
        $options['platform']="BJ";
        $options['is_debug']=true;
        $options['skip_setting_file']=true;
        $options['error_exception']=null;
        $options['error_500']=null;
        $options['error_404']=null;
        $options['error_debug']=null;
        $options['skip_view_notice_error']=true;
        $options['use_super_global']=true;
        $options['ext']=[
            'noclass'=>true,
            AppTestObject::class=>false,
            AppTestObjectA::class=>true,
            AppTestObjectB::class=>['aa'=>'22'],
        ];
        
        $flag=App::RunQuickly($options,function(){
            App::G()->addBeforeRunHandler(function(){ echo "addBeforeRunHandler";});
            App::G()->addBeforeShowHandler(function(){ echo "beforeShowHandlers";});
            $value = $cache[$key];
        });
        App::G()->cleanUp();
        App::G()->cleanAll();
        
        $options=[
            'skip_setting_file'=>true,
            'reload_for_flags'=>false,
        ];
            $options['error_exception']=null;

            $options['error_500']=null;
        $options['error_404']=null;
        $options['error_debug']=null;
        
        App::G(new App())->init($options);
        $this->do3();
        $this->do2();
        $this->doSystemWrapper();

        App::G()->extendComponents(AppTest::class,['Foo'],['V',"ZZZ"]);
        
        \MyCodeCoverage::G()->end(App::class);
        $this->assertTrue(true);
    }
    public function doSystemWrapper()
    {
        App::system_wrapper_get_providers();

        App::header($output,$replace = true, $http_response_code=0);
        App::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
        App::exit_system($code=0);
        App::set_exception_handler(function($handler){
        return set_exception_handler($handler);
        });
        App::register_shutdown_function(function(){echo "shutdowning";});
        /*
        App::header($output,$replace = true, $http_response_code=0);
        App::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
        App::exit_system($code=0);
        App::set_exception_handler(function($handler){
            return set_exception_handler($handler);
        });
        App::register_shutdown_function(function(){echo "shutdowning";});
        */
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
        Configer::G()->init($options);
        $key='key';
        $file_basename='config';
        
        App::Setting($key);
        App::Config($key, $file_basename);
        App::LoadConfig($file_basename);
        
        ////
        App::DumpTrace();
        App::Dump("OK");
        ////
        
        
        $str='<>';
        $url="";
        $method="method";
        App::H($str);
        App::H(['a'=>'b']);
        App::H(123);
        
        App::URL($url=null);
        
        App::Parameters();
        App::getRouteCallingMethod();
        App::setRouteCallingMethod($method);
        //*/
        //*
        $path_view=\GetClassTestPath(View::class);

        $options=[
            'path_view'=>$path_view,
        ];
        View::G()->init($options);
        
        App::G()->addBeforeShowHandler(function(){ echo "addBeforeShowHandler";});
        App::G()->options['skip_view_notice_error']=true;
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
        
        App::G()->exit_handler=function($code){
            var_dump(DATE(DATE_ATOM));
        };
        //*
        App::ExitRedirect($url, $only_in_site=true);
        App::ExitRedirect("http://www.github.com",true);
        App::ExitRouteTo($url);
        App::Exit404();
         App::G()->is_debug=true;
        App::ExitJson($ret);
        //*/
        

        
        
        $classes=[];
        $callback=function($code){
            var_dump(DATE(DATE_ATOM));
        };
        App::assignExceptionHandler($classes, $callback);
        App::setMultiExceptionHandler($classes, $callback);
        App::setDefaultExceptionHandler($callback);
        
        $k="k";$v="v";
        $class_name=AppTestObject::class;
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
        
        App::assignPathNamespace("NoPath","NoName");
        App::addRouteHook(function(){});
        App::stopRunDefaultHandler();
        
        App::IsRunning();
        App::IsDebug();
        App::Platform();
        App::IsInException();
    }
}
class AppTestObject
{
    static $x;
    use SingletonEx;

    public static function Foo()
    {
        return "OK";
    }
}
class AppTestObjectA
{
    static $x;
    use SingletonEx;
    public static function Foo()
    {
        return "OK";
    }
    public function init($options,$context)
    {
    }
}
class AppTestObjectB
{
    static $x;
    use SingletonEx;
    public static function Foo()
    {
        return "OK";
    }
    public function init($options,$context)
    {
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