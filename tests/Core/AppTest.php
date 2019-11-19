<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\App;
use DNMVCS\DNMVCS;
use DNMVCS\Core\Configer;
use DNMVCS\Core\View;
use DNMVCS\Core\SingletonEx;

class AppTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $path_app=\GetClassTestPath(App::class);
        
        \MyCodeCoverage::G()->begin(App::class);
        
        $path_app=\GetClassTestPath(App::class);
        $path_config=\GetClassTestPath(Configer::class);
        
        $options=[
            'path' => $path_app,
            'path_config' => $path_config,
            'path_view' => $path_app.'view/',

            'platform' => 'BJ',
            'is_debug' => true,
            'skip_setting_file' => true,
            'reload_for_flags' => false,
            'error_exception' => NULL,
            'error_500' => NULL,
            'error_404' => NULL,
            'error_debug' => NULL,
            'skip_view_notice_error' => true,
            'use_super_global' => true,
        ];
        $options['ext']=[
            'noclass'=>true,
            AppTestObject::class=>false,
            AppTestObjectA::class=>true,
            AppTestObjectB::class=>['aa'=>'22'],
        ];
        App::RunQuickly($options,function(){
            //App::G()->addBeforeRunHandler(function(){ echo "addBeforeRunHandler";});
            App::G()->addBeforeShowHandler(function(){ echo "beforeShowHandlers";});
            $value = $cache[$key]; // trigger notice
            

        App::G()->options['error_debug']='_sys/error-debug';
        $value = $cache[$key]; 
        
        App::G()->options['error_debug']=function($data){var_dump($data);return;};
        $value = $cache[$key]; 
        
        App::G()->is_debug=false;
        $value = $cache[$key]; 
        App::G()->is_debug=true;

        });
        App::G()->getStaticComponentClasses();
        
        App::SG()->_SERVER['PATH_INFO']='/NOOOOOOOOOOOOOOO';
        App::G()->options['error_404']=function(){
            echo "nooooooooooooo\n";
            
        };
        App::G()->run();
        App::G()->clear();
        App::G()->cleanAll();

            $path_app=\GetClassTestPath(App::class);

        $options=[
            // 'no this path' => $path_app,
            'path_config' => $path_app,
            'override_class'=>'\\'.App::class,
            'path_view' => $path_app.'view/',

            'error_debug' => NULL,
            'error_exception' => NULL,
            'error_500' => NULL,
        ];
        View::G(new View());
        App::G(new App())->init($options);
        $this->do3();
        $this->do2();
        $this->doSystemWrapper();

        App::G()->extendComponents(AppTest::class,['Foo'],['V',"ZZZ"]);
        
        $this->do4();


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
        
        App::G()->system_wrapper_replace([
            'header' =>function(){ echo "change!\n";},
            'setcookie' =>function(){ echo "change!\n";},
            'exit_system' =>function(){ echo "change!\n";},
            'set_exception_handler' =>function(){ echo "change!\n";},
            'register_shutdown_function' =>function(){ echo "change!\n";},
        ]);
        
        App::header($output,$replace = true, $http_response_code=0);
        App::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
        App::exit_system($code=0);
        App::set_exception_handler(function($handler){
            return set_exception_handler($handler);
        });
        App::register_shutdown_function(function(){echo "shutdowning";});
        
    }
    public function do3()
    {       
        App::G()->options['error_exception']="_sys/error-exception";
        App::OnException(new \Exception("333333",-1));
        
        App::G()->options['error_exception']=null;
        App::OnException(new \Exception("EXxxxxxxxxxxxxxx",-1));
        
        App::G()->options['error_exception']=function($ex){ echo $ex;};

        App::OnException(new \Exception("22222222222222",-1));
        App::assignExceptionHandler(\Exception::class,function($ex){
            App::OnException($ex);
            echo "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
        });
        App::OnException(new \Exception("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA",-1));
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
        $path_view=\GetClassTestPath(App::class).'view/';

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
        App::G()->system_wrapper_replace(['exit_system'=>function($code){
            var_dump(DATE(DATE_ATOM));
        }]);
        //*
        App::ExitRedirect($url);
        App::ExitRedirect('http://www.github.com');

        App::ExitRedirectOutside("http://www.github.com",true);
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
        
        App::IsRunning();
        App::IsDebug();
        App::IsRealDebug();
        App::Platform();
        App::IsInException();
        
        
        
    }
    protected function do4()
    {
        
        
    echo "-----------------------\n";
        $path_app=\GetClassTestPath(App::class);
        $path_config=\GetClassTestPath(Configer::class);
        $options=[
            'path' => $path_app,
            'path_config' => $path_config,
            'platform' => 'BJ',
            'is_debug' => true,
            'skip_setting_file' => true,
            'reload_for_flags' => true,
            'error_exception' => NULL,
            'error_500' => NULL,
            'error_404' => NULL,
            'error_debug' => NULL,
            'skip_view_notice_error' => true,
            'use_super_global' => true,
            'override_class'=>'\\'.AppTestApp::class,
        ];
        DNMVCS::G(new DNMVCS())->init($options);
        DNMVCS::G()->getStaticComponentClasses();
        
        App::G()->getDynamicComponentClasses();
        
        App::G()->addDynamicComponentClass($class);
        App::G()->deleteDynamicComponentClass($class);

        
        $options=[
            'path' => $path_app,
        'skip_setting_file' => true,
        'is_debug'=>false,
        'error_exception' => NULL,
        'error_500' => NULL,
        'error_404' => NULL,
        'error_debug' => NULL,
        ];
        AppTestApp::RunQuickly($options);
        
        AppTestApp::G()->options['error_404']='_sys/error-404';
        AppTestApp::On404();
        
        AppTestApp::G()->addRouteHook(function(){
            throw new \Exception("xxx");
        });
        AppTestApp::G()->run();
        
        AppTestApp2::RunQuickly([]);
    }
}
class AppTestApp extends App
{
    protected function onInit()
    {
        $this->addBeforeRunHandler(function(){
            static::DumpTrace();
            static::Dump("ABC");
            var_dump("!");
            return true;
        });
        return parent::onInit();
    }
}
class AppTestApp2 extends App
{
    protected function onInit()
    {
        //throw new \Exception("zzzzzzzzzzzz");
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