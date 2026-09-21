<?php
namespace tests\DuckPhp\Foundation\System;

use DuckPhp\Foundation\System\Helper;
use DuckPhp\DuckPhp;
use DuckPhp\Core\SingletonExTrait as SingletonExTrait;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Helper::class);
        
        Helper::addRouteHook(function(){},'append-outter',true);
        
        Helper::CallException(new \Exception("333333",-1));

        Helper::IsRunning();
        Helper::isInException();

        Helper::set_exception_handler(function($handler){
            return set_exception_handler($handler);
        });
        Helper::register_shutdown_function(function(){echo "shutdowning";});

        $k="k";$v="v";
        $class_name=HelperFakeSessionHandler::class;
        $var_name="x";
         
        
        $output="";

        
        Helper::system_wrapper_replace([
            'header' =>function(){ echo "change!\n";},
            'setcookie' =>function(){ echo "change!\n";},
            'exit' =>function(){ echo "change!\n";},
        ]);
        Helper::system_wrapper_get_providers();

        Helper::header($output,$replace = true, $http_response_code=0);
        Helper::setcookie( $key="123",  $value = '',  $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
        Helper::exit($code=0);
        
        
        Helper::session_start($options=[]);
        Helper::session_id(null);
        Helper::session_destroy();
        $handler = new HelperFakeSessionHandler();
        Helper::session_set_save_handler( $handler);

        
        
        ////[[[[
        Helper::SESSION();
        Helper::FILES();
        Helper::CookieSet ('a','b');
        Helper::CookieGet ('a','b');
        Helper::SessionSet('c','d');
        Helper::SessionGet('c');
        Helper::SessionUnset('c');
        //Helper::OnEvent('MyEvent',[static::class, 'callit']);
        //App::FireEvent('MyEvent','A','B','C');
        Helper::mime_content_type('x.jpg');

        ////]]]]
        
        ////[[[[
        $this->do_Core_Component();
        
        Helper::getViewData();
        Helper::DbCloseAll();
        $old_class = HelperTestObject::class;
        $new_class = HelperTestObject::class;
        Helper::replaceController($old_class, $new_class);
        
        ////
        Helper::setBeforeGetDbHandler(null);
        Helper::getRouteMaps();
        Helper::assignRoute('ab/c',['z']);
        Helper::assignImportantRoute('ab/c',['z']);
        try{
            Helper::Redis();
        }catch(\Throwable $ex){}
        
        Helper::assignRewrite('zxvf', 'zz');
        Helper::getRewrites();
        Helper::RemoveEvent('nullEnvent');
        Helper::getCliParameters();
        ////]]]]
                
        Helper::OnGlobalEvent('MyEvent',function(){});
        Helper::FireGlobalEvent('MyEvent',function(){});
        //try {
            Helper::saveExtOptions(['xdata'=>DATE(DATE_ATOM),"installed"=>"a"]);
        //} catch (\Throwable $ex) {
        //try {
            Helper::ProjectThrowOn(false,"An Error");
            Helper::ThrowOn(false,"An Error");
        //} catch (\Throwable $ex) {
        //}
        \LibCoverage\LibCoverage::End();

    }
    protected function do_Core_Component()
    {

        $new_namespace=__NAMESPACE__;
        $new_namespace.='\\';
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);

        $options=[
            'path' => $path_app,
            'is_debug' => true,
            'namespace'=> __NAMESPACE__,
        ];
        DuckPhp::_()->init($options);
        //Helper::addBeforeShowHandler(function(){});
        

    }
}
class HelperFakeSessionHandler implements \SessionHandlerInterface
{
    static $x;

    public function open($savePath, $sessionName): bool
    {
        return true;
    }
    public function close(): bool
    {
        return true;
    }
    public function read($id): string
    {
        return '';
    }
    public function write($id, $data): bool
    {
        return true;
    }
    public function destroy($id): bool
    {
        return true;
    }
    public function gc($maxlifetime): int
    {
        return 0;
    }
}
class HelperTestObject
{
    static $x;
    use SingletonExTrait;

    public static function Foo()
    {
        return "OK";
    }
}
