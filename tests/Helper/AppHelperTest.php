<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\AppHelper;
use DuckPhp\Core\App;
use DuckPhp\SingletonEx\SingletonEx;

class AppHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(AppHelper::class);
        
        AppHelper::assignPathNamespace(__DIR__,'NoExistsByAppHelper');
        AppHelper::setUrlHandler(function($url){});
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
        
        AppHelper::GLOBALS($k, $v=null);
        AppHelper::STATICS($k, $v=null);
        AppHelper::CLASS_STATICS($class_name, $var_name);        
        
                $output="";

        
        App::G()->system_wrapper_replace([
            'header' =>function(){ echo "change!\n";},
            'setcookie' =>function(){ echo "change!\n";},
            'exit' =>function(){ echo "change!\n";},
        ]);
        
        AppHelper::header($output,$replace = true, $http_response_code=0);
        AppHelper::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
        AppHelper::exit($code=0);
        
        
        AppHelper::session_start($options=[]);
        AppHelper::session_id(null);
        AppHelper::session_destroy();
        $handler=new HelperFakeSessionHandler();
        AppHelper::session_set_save_handler( $handler);

        AppHelper::add404RouteHook( function(){var_dump('404!');});
        ////[[[[
        $this->do_Core_Component();
        
        AppHelper::getViewData();
        
        $old_class = AppHelperTestObject::class;
        $new_class = AppHelperTestObject::class;
        AppHelper::replaceControllerSingelton($old_class, $new_class);
        ////]]]]
        \MyCodeCoverage::G()->end();

    }
    protected function do_Core_Component()
    {

        AppHelper::getStaticComponentClasses();
        AppHelper::getDynamicComponentClasses();
        $class="NoExits";
        AppHelper::addDynamicComponentClass($class);
        AppHelper::removeDynamicComponentClass($class);
        
        
        $new_namespace=__NAMESPACE__;
        $new_namespace.='\\';
    
        $options=[
            //'path' => $path_app,
            'is_debug' => true,
            'namespace'=> __NAMESPACE__,
        ];
        App::G()->init($options);
        AppHelper::addBeforeShowHandler(function(){});

        AppHelper::extendComponents(['Foo'=>[static::class,'Foo']],['V',"ZZZ"]);
        AppHelper::cloneHelpers($new_namespace);
        AppHelper::cloneHelpers($new_namespace, ['M'=>'no_exits_class']);
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
class AppHelperTestObject
{
    static $x;
    use SingletonEx;

    public static function Foo()
    {
        return "OK";
    }
}