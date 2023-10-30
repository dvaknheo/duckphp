<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\AppHelperTrait;
use DuckPhp\Core\App;
use DuckPhp\SingletonEx\SingletonExTrait;

class AppHelperTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AppHelperTrait::class);
        
        AppHelper::addRouteHook(function(){},'append-outter',true);
        
        AppHelper::CallException(new \Exception("333333",-1));

        AppHelper::IsRunning();
        AppHelper::isInException();

        AppHelper::set_exception_handler(function($handler){
            return set_exception_handler($handler);
        });
        AppHelper::register_shutdown_function(function(){echo "shutdowning";});

        $k="k";$v="v";
        $class_name=HelperFakeSessionHandler::class;
        $var_name="x";
         
        
        $output="";

        
        AppHelper::system_wrapper_replace([
            'header' =>function(){ echo "change!\n";},
            'setcookie' =>function(){ echo "change!\n";},
            'exit' =>function(){ echo "change!\n";},
        ]);
        AppHelper::system_wrapper_get_providers();

        AppHelper::header($output,$replace = true, $http_response_code=0);
        AppHelper::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
        AppHelper::exit($code=0);
        
        
        AppHelper::session_start($options=[]);
        AppHelper::session_id(null);
        AppHelper::session_destroy();
        $handler = new HelperFakeSessionHandler();
        AppHelper::session_set_save_handler( $handler);

        
        
        ////[[[[
        AppHelper::SESSION();
        AppHelper::FILES();
        try{
            AppHelper::Event();
        }catch(\Exception $ex){
        }
        AppHelper::CookieSet ('a','b');
        AppHelper::CookieGet ('a','b');
        AppHelper::SessionSet('c','d');
        AppHelper::SessionGet('c');
        AppHelper::SessionUnset('c');
        //AppHelper::OnEvent('MyEvent',[static::class, 'callit']);
        //App::FireEvent('MyEvent','A','B','C');
        AppHelper::mime_content_type('x.jpg');

        ////]]]]
        
        ////[[[[
        $this->do_Core_Component();
        
        AppHelper::getViewData();
        AppHelper::DbCloseAll();
        $old_class = AppHelperTestObject::class;
        $new_class = AppHelperTestObject::class;
        AppHelper::replaceController($old_class, $new_class);
        
        ////
        AppHelper::setBeforeGetDbHandler(null);
        AppHelper::getRoutes();
        AppHelper::assignRoute('ab/c',['z']);
        AppHelper::assignImportantRoute('ab/c',['z']);
        try{
            AppHelper::Redis();
        }catch(\TypeError $ex){}
        
        AppHelper::assignRewrite('zxvf', 'zz');
        AppHelper::getRewrites();


        ////]]]]
        \LibCoverage\LibCoverage::End();

    }
    protected function do_Core_Component()
    {

        $new_namespace=__NAMESPACE__;
        $new_namespace.='\\';
    
        $options=[
            //'path' => $path_app,
            'is_debug' => true,
            'namespace'=> __NAMESPACE__,
        ];
        App::_()->init($options);
        //AppHelper::addBeforeShowHandler(function(){});
        

    }
}
class AppHelper
{
    use AppHelperTrait;
}
class HelperFakeSessionHandler implements \SessionHandlerInterface
{
    static $x;

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
class AppHelperTestObject
{
    static $x;
    use SingletonExTrait;

    public static function Foo()
    {
        return "OK";
    }
}