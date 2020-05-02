<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\AppHelper;
use DuckPhp\Core\App;

class AppHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(AppHelper::class);
        
        AppHelper::assignPathNamespace(__DIR__,'NoExistsByAppHelper');
        AppHelper::setUrlHandler(function($url){});
        AppHelper::addRouteHook(function(){},'append-outter',true);
        
        AppHelper::OnException(new \Exception("333333",-1));

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

        \MyCodeCoverage::G()->end(AppHelper::class);
        $this->assertTrue(true);
        /*
        //*/
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