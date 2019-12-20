<?php
namespace tests\DuckPhp\Core{

use DuckPhp\Core\App;
use DuckPhp\App as DuckPhp;
use DuckPhp\Core\Configer;
use DuckPhp\Core\View;
use DuckPhp\Core\SingletonEx;

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
            App::G()->addBeforeShowHandler(function(){ echo "beforeShowHandlers";});
            App::G()->setBeforeRunHandler(function(){ echo "setBeforeRunHandler";});
            App::G()->setAfterRunHandler(function(){ echo "setAfterRunHandler";});
            
            $value = $cache[$key]; // trigger notice
            App::G()->options['error_debug']='_sys/error-debug';
            $value = $cache[$key]; 
            
            App::G()->options['error_debug']=function($data){var_dump($data);return;};
            $value = $cache[$key]; 
            
            App::G()->is_debug=false;
            $value = $cache[$key]; 
            App::G()->is_debug=true;

        });
        
        App::SG()->_SERVER['PATH_INFO']='/NOOOOOOOOOOOOOOO';
        App::G()->options['error_404']=function(){
            echo "nooooooooooooo\n";
            
        };
        App::G()->run();
        App::G()->clear();

            $path_app=\GetClassTestPath(App::class);

        $options=[
            // 'no this path' => $path_app,
            'path_config' => $path_app,
            'override_class'=>'\\'.App::class,
            'path_view' => $path_app.'view/',
            'is_debug' => true,
        ];
        View::G(new View());
        Configer::G(new Configer());
        App::G(new App())->init($options);
        $this->doException();
        $this->doGlue();
        $this->doSystemWrapper();

        App::G()->extendComponents(['Foo'=>[AppTest::class,'Foo']],['V',"ZZZ"]);
        
        $this->do4();
        $this->doPugins();
        $this->do_Core_Component();



        $appended=function () {
            App::G()->forceFail();
            return true;
        };
         $appended=function () {
            App::G()->forceFail();
            return true;
        };
        App::G()->setBeforeRunHandler($appended);
        App::G()->run();
        App::G()->setBeforeRunHandler(null);
        App::G()->setAfterRunHandler($appended);
        App::G()->run();
        
        \MyCodeCoverage::G()->end(App::class);
        $this->assertTrue(true);
    }
    public function doPugins()
    {
        $app=new App();
        $options=['plugin_mode'=>true];
        try{
            $app->init($options,$app);
        }catch(\Exception $ex){
            echo $ex->getMessage();
        }
        $new_namespace=__NAMESPACE__;
        $new_namespace.='\\';
        App::G()->cloneHelpers($new_namespace, $componentClassMap);
        App::G()->cloneHelpers($new_namespace, ['M'=>'no_exits_class']);
    }
    public function doSystemWrapper()
    {
        App::system_wrapper_get_providers();

        App::header($output,$replace = true, $http_response_code=0);
        App::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
        App::exit($code=0);
        App::set_exception_handler(function($handler){
            return set_exception_handler($handler);
        });
        App::register_shutdown_function(function(){echo "shutdowning";});
        
        App::G()->system_wrapper_replace([
            'header' =>function(){ echo "change!\n";},
            'setcookie' =>function(){ echo "change!\n";},
            'exit' =>function(){ echo "change!\n";},
            'set_exception_handler' =>function(){ echo "change!\n";},
            'register_shutdown_function' =>function(){ echo "change!\n";},
        ]);
        
        App::header($output,$replace = true, $http_response_code=0);
        App::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
        App::exit($code=0);
        App::set_exception_handler(function($handler){
            return set_exception_handler($handler);
        });
        App::register_shutdown_function(function(){echo "shutdowning";});
        
    }
    public function doException()
    {       
        App::G()->options['error_500']="_sys/error-exception";
        App::OnException(new \Exception("333333",-1));
        
        App::G()->options['error_500']=null;
        App::OnException(new \Exception("EXxxxxxxxxxxxxxx",-1));
        
        App::G()->options['error_500']=function($ex){ echo $ex;};

        App::OnException(new \Exception("22222222222222",-1));
        App::assignExceptionHandler(\Exception::class,function($ex){
            App::OnException($ex);
            echo "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
        });
        App::OnException(new \Exception("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA",-1));
    }
    public function doGlue()
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
        App::trace_dump();
        App::var_dump("OK");
        ////
        
        
        $str='<>';
        $url="";
        $method="method";
        App::H($str);
        App::H(['a'=>'b']);
        App::H(123);
        App::L("a{b}c",[]);
        App::L("a{b}c",['b'=>'123']);
        App::HL("&<{b}>",['b'=>'123']);
        App::Domain();
        
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
        App::G()->system_wrapper_replace(['exit'=>function($code){
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
        App::addRouteHook(function(){},'append-outter',true);
        
        App::IsRunning();
        App::IsDebug();
        App::IsRealDebug();
        App::Platform();
        App::IsInException();
        App::getPathInfo();
        try{
            App::Pager();
        }catch(\Throwable $ex){
            //
        }
        App::Logger();
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
        DuckPhp::G(new DuckPhp())->init($options);

        
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
        },'append-outter',true);
        AppTestApp::G()->run();
        
        AppTestApp2::RunQuickly([]);
    }
    protected function do_Core_Component()
    {
        App::G()->getStaticComponentClasses();
        App::G()->getDynamicComponentClasses();
        $class="NoExits";
        App::G()->addDynamicComponentClass($class);
        App::G()->removeDynamicComponentClass($class);
    }
}
class AppTestApp extends App
{
    protected function onInit()
    {
        $this->setBeforeRunHandler(function(){
            static::trace_dump();
            static::var_dump("ABC");
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

}
namespace tests\DuckPhp\Core\Helper{
class ControllerHelper
{
    use \DuckPhp\Core\Helper\HelperTrait;
}
}