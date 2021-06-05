<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\AdvanceHelper;
use DuckPhp\Helper\AdvanceHelperTrait;
use DuckPhp\Core\App;
use DuckPhp\SingletonEx\SingletonExTrait;

class AdvanceHelperTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AdvanceHelperTrait::class);
        
        AdvanceHelper::assignPathNamespace(__DIR__,'NoExistsByAdvanceHelper');
        AdvanceHelper::addRouteHook(function(){},'append-outter',true);
        
        AdvanceHelper::CallException(new \Exception("333333",-1));

        AdvanceHelper::IsRunning();
        AdvanceHelper::isInException();

        AdvanceHelper::set_exception_handler(function($handler){
            return set_exception_handler($handler);
        });
        AdvanceHelper::register_shutdown_function(function(){echo "shutdowning";});

        $k="k";$v="v";
        $class_name=HelperFakeSessionHandler::class;
        $var_name="x";
         
        
        $output="";

        
        App::G()->system_wrapper_replace([
            'header' =>function(){ echo "change!\n";},
            'setcookie' =>function(){ echo "change!\n";},
            'exit' =>function(){ echo "change!\n";},
        ]);
        
        AdvanceHelper::header($output,$replace = true, $http_response_code=0);
        AdvanceHelper::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
        AdvanceHelper::exit($code=0);
        
        
        AdvanceHelper::session_start($options=[]);
        AdvanceHelper::session_id(null);
        AdvanceHelper::session_destroy();
        $handler=new HelperFakeSessionHandler();
        AdvanceHelper::session_set_save_handler( $handler);

        AdvanceHelper::add404RouteHook( function(){var_dump('404!');});
        
        
        ////[[[[
        AdvanceHelper::SESSION();
        AdvanceHelper::FILES();
        try{
            AdvanceHelper::Event();
        }catch(\Exception $ex){
        }
        AdvanceHelper::CookieSet ('a','b');
        AdvanceHelper::SessionSet('c','d');
        AdvanceHelper::OnEvent('MyEvent',[static::class, 'callit']);
        App::FireEvent('MyEvent','A','B','C');
    
        ////]]]]
        
        ////[[[[
        $this->do_Core_Component();
        
        AdvanceHelper::getViewData();
        
        $old_class = AdvanceHelperTestObject::class;
        $new_class = AdvanceHelperTestObject::class;
        AdvanceHelper::replaceControllerSingelton($old_class, $new_class);
        ////]]]]
        \LibCoverage\LibCoverage::End();

    }
    protected function do_Core_Component()
    {
        AdvanceHelper::getDynamicComponentClasses();
        $class="NoExits";
        AdvanceHelper::addDynamicComponentClass($class);
        
        
        $new_namespace=__NAMESPACE__;
        $new_namespace.='\\';
    
        $options=[
            //'path' => $path_app,
            'is_debug' => true,
            'namespace'=> __NAMESPACE__,
        ];
        App::G()->init($options);
        AdvanceHelper::addBeforeShowHandler(function(){});

        AdvanceHelper::extendComponents(['Foo'=>[static::class,'Foo']],['V',"ZZZ"]);
        AdvanceHelper::cloneHelpers($new_namespace);
        AdvanceHelper::cloneHelpers($new_namespace, ['M'=>'no_exits_class']);
    }
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
class AdvanceHelperTestObject
{
    static $x;
    use SingletonExTrait;

    public static function Foo()
    {
        return "OK";
    }
}