<?php
namespace tests{

class FullTest extends \PHPUnit\Framework\TestCase
{

    //*
    public function testMain()
    {
        /////
        $options=[];
        $options['skip_setting_file']=true;
        $options['error_exception']=null;
        $options['error_500']=null;
        $options['error_404']=null;
        $flag=\DNMVCS\DNMVCS::RunQuickly($options);
        $this->assertFalse($flag);
    }
    //*/
    public function testSecond()
    {
        $_SERVER['argv']=[];
        $options=[];
        $options['path']=realpath(__DIR__);
        $options['skip_setting_file']=true;
        $options['error_exception']=null;
        $options['error_500']=null;
        $options['namespace']='MySpace';
        $options['error_404']=null;
        $flag=\DNMVCS\DNMVCS::RunQuickly($options,function(){

        });
        $this->assertTrue($flag);
    }
    /*

    public function test3()
    {
        \DNMVCS\DNMVCS::G(new \DNMVCS\DNMVCS())->init(['overrid_class'=>'DNMVCS\DNMVCS','skip_setting_file'=>true])->stopRunDefaultHandler();
        $this->assertTrue(true);
    }
        public function test3(){
    
        define('DNMVCS_SINGLETONEX_REPALACER', \GSingleton::class . '::'.'SingletonInstance');
        \T::G();
        $this->assertTrue(true);
    }
    public function test4()
    {
        \DNMVCS\DNMVCS::SG();
    }
    //*/
}
}
namespace {
class T
{
    use \DNMVCS\Core\SingletonEx;
}
class GSingleton
{
    public static $_instances=[];
    
    public static function ReplaceDefaultSingletonHandler()
    {
        if (defined('DNMVCS_SINGLETONEX_REPALACER')) {
            return false;
        }
        define('DNMVCS_SINGLETONEX_REPALACER', self::class . '::'.'SingletonInstance');
        return true;
    }
    public static function SingletonInstance($class, $object)
    {
        static::$_instances[$class]= ($object)?$object:( (static::$_instances[$class])??new $class() );
        return static::$_instances[$class];
    }
}
}
namespace MySpace\Base
{
    class App extends \DNMVCS\DNMVCS
    {
        protected function onInit()
        {
            return parent::onInit();
        }
    }
}
namespace MySpace\Base\Helper
{
    class ControllerHelper extends \DNMVCS\Helper\ControllerHelper
    {
    }
    class ServiceHelper extends \DNMVCS\Helper\ServiceHelper
    {
        
    }
    class ModelHelper extends \DNMVCS\Helper\ModelHelper
    {
        
    }
     class ViewHelper extends \DNMVCS\Helper\ViewHelper
    {
        
    }
}
namespace MySpace\Controller
{
    use MySpace\Base\Helper\ControllerHelper as C;
    class Main {
        public function index()
        {
            $ret=C::IsDebug();
            $ret=C::Platform();
            
            $ret=C::URL('ABC');
            $ret=C::H('<>');


/*
V::H();
V::ShowBlock();
V::DumpTrace();
V::Dump();
V::IsDebug();
V::Platform();
V::AssignExtendStaticMethod();
V::GetExtendStaticStaticMethodList();
V::CallExtendStaticMethod();
V::__callStatic();
V::ThrowOn();

M::DB();
M::DB_W();
M::DB_R();
M::IsDebug();
M::Platform();
M::AssignExtendStaticMethod();
M::GetExtendStaticStaticMethodList();
M::CallExtendStaticMethod();
M::__callStatic();
M::ThrowOn();

S::Setting();
S::Config();
S::LoadConfig();
S::IsDebug();
S::Platform();
S::AssignExtendStaticMethod();
S::GetExtendStaticStaticMethodList();
S::CallExtendStaticMethod();
S::__callStatic();
S::ThrowOn();

C::Import();
C::RecordsetUrl();
C::RecordsetH();
C::Pager();
C::MapToService();
C::explodeService();
C::Setting();
C::Config();
C::LoadConfig();
C::H();
C::URL();
C::Parameters();
C::getRouteCallingMethod();
C::setRouteCallingMethod();
C::Show();
C::ShowBlock();
C::setViewWrapper();
C::assignViewData();
C::ExitRedirect();
C::ExitRouteTo();
C::Exit404();
C::ExitJson();
C::header();
C::exit_system();
C::assignExceptionHandler();
C::setMultiExceptionHandler();
C::setDefaultExceptionHandler();
C::SG();
C::GLOBALS();
C::STATICS();
C::CLASS_STATICS();
C::session_start();
C::session_id();
C::session_destroy();
C::session_set_save_handler();
C::IsDebug();
C::Platform();
C::AssignExtendStaticMethod();
C::GetExtendStaticStaticMethodList();
C::CallExtendStaticMethod();
C::__callStatic();
C::ThrowOn();

App::RunQuickly();
App::_EmptyFunction();
App::G();
App::ThrowOn();
App::AssignExtendStaticMethod();
App::GetExtendStaticStaticMethodList();
App::CallExtendStaticMethod();
App::__callStatic();
App::On404();
App::OnException();
App::OnDevErrorHandler();
App::IsRunning();
App::URL();
App::Parameters();
App::ShowBlock();
App::Setting();
App::Config();
App::LoadConfig();
App::DumpTrace();
App::Dump();
App::ExitJson();
App::ExitRedirect();
App::ExitRouteTo();
App::Exit404();
App::header();
App::setcookie();
App::exit_system();
App::set_exception_handler();
App::register_shutdown_function();
App::system_wrapper_get_providers();
App::Platform();
App::IsDebug();
App::isInException();
App::Show();
App::H();
App::SG();
App::GLOBALS();
App::STATICS();
App::CLASS_STATICS();
App::session_start();
App::session_destroy();
App::session_set_save_handler();

//*/
        }
    }

}
namespace MySpace
{
    class MyService
    {
        public function doSomeThing()
        {
            M::DB();
            M::DB_W();
            M::DB_R();
            M::IsDebug();
            M::Platform();
            M::GetExtendStaticStaticMethodList();
            M::ThrowOn();
            
            M::AssignExtendStaticMethod();
            M::CallExtendStaticMethod();
            M::__callStatic();
            
        }
    }
    class MyModel
    {
        public function doSomeThing()
        {
            M::DB();
            M::DB_W();
            M::DB_R();
            M::IsDebug();
            M::Platform();
            M::GetExtendStaticStaticMethodList();
            M::ThrowOn();
            
            M::AssignExtendStaticMethod();
            M::CallExtendStaticMethod();
            M::__callStatic();
            
        }
    }
}

