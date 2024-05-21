<?php 
namespace tests\DuckPhp\Core;
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\ExitException;

class SystemWrapperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SystemWrapper::class);
        
        //SystemWrapper::_()->system_wrapper_replace(array $funcs);
        $data=SystemWrapperObject::system_wrapper_get_providers();
        
        SystemWrapperObject::var_dump(DATE(DATE_ATOM));
        SystemWrapperObject::system_wrapper_replace(['var_dump'=>function(...$args){var_dump("!!!!");}]);
        SystemWrapperObject::var_dump(DATE(DATE_ATOM));
        SystemWrapperObject::var_dump2(DATE(DATE_ATOM));
        
        var_dump($data);
        
        $this->doSystemWrapper();

        /////[[[[
        define('__SYSTEM_WRAPPER_REPLACER', SystemWrapperObject2::class);
        SystemWrapperObject::var_dump("zz");
        /////]]]]
        \LibCoverage\LibCoverage::End();

    }
public function doSystemWrapper()
{
    SystemWrapperObject::system_wrapper_get_providers();
    $output="";

    SystemWrapperObject::header($output,$replace = true, $http_response_code=0);
    SystemWrapperObject::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
   
    SystemWrapperObject::set_exception_handler(function($handler){
        return set_exception_handler($handler);
    });
    SystemWrapperObject::register_shutdown_function(function(){echo "shutdowning";});
    
    SystemWrapperObject::session_start([]);
    try{
    SystemWrapperObject::session_id(md5('123456'));
    }catch(\ErrorException $ex){
    }
    SystemWrapperObject::session_id(null);
    SystemWrapperObject::session_destroy();
    $handler=new FakeSessionHandler2();
    SystemWrapperObject::session_set_save_handler( $handler);
    
    SystemWrapperObject::mime_content_type('x.jpg');
    
    ExitException::Init();
    try{
    SystemWrapperObject::exit(-5);
    }catch(\Exception $ex){}
    SystemWrapperObject::_()->system_wrapper_replace([
        'mime_content_type' =>function(){ echo "change!\n";},
        'header' =>function(){ echo "change!\n";},
        'setcookie' =>function(){ echo "change!\n";},
        'exit' =>function(){ echo "change!\n";},
        'set_exception_handler' =>function(){ echo "change!\n";},
        'register_shutdown_function' =>function(){ echo "change!\n";},
        'session_start' => function(){ echo "change!\n";},
        'session_id' =>  function(){ echo "change!\n";},
        'session_destroy' => function(){ echo "change!\n";},
        'session_set_save_handler' => function(){ echo "change!\n";},
    ]);
    SystemWrapperObject::mime_content_type('test');
    SystemWrapperObject::header($output,$replace = true, $http_response_code=0);
    SystemWrapperObject::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
    SystemWrapperObject::exit($code=0);
    SystemWrapperObject::set_exception_handler(function($handler){
        return set_exception_handler($handler);
    });
    SystemWrapperObject::register_shutdown_function(function(){echo "shutdowning";});
    
    
    SystemWrapperObject::session_start([]);
    SystemWrapperObject::session_id(null);
    SystemWrapperObject::session_id(md5('123456'));
    SystemWrapperObject::session_destroy();
    $handler=new FakeSessionHandler2();
    SystemWrapperObject::session_set_save_handler( $handler);
    
    
    
}
}


class SystemWrapperObject extends SystemWrapper
{
    protected $system_handlers = [
        'header' => null,
        'setcookie' => null,
        'exit' => null,
        'set_exception_handler' => null,
        'register_shutdown_function' => null,
        
        'session_start' => null,
        'session_id' => null,
        'session_destroy' => null,
        'session_set_save_handler' => null,
        'mime_content_type' => null,
        'var_dump'=>null,
        'var_dump2'=>null,

    ];
    
    public static function var_dump(...$args)
    {
        return static::_()->_var_dump(...$args);
    }
    public function _var_dump(...$args)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        echo "BEGIN";
        var_dump(...$args);
    }
    public static function var_dump2(...$args)
    {
        return static::_()->_var_dump2(...$args);
    }
    public function _var_dump2(...$args)
    {
        $this->system_wrapper_call('var_export', func_get_args());
        try{
        $this->system_wrapper_call('ttt', func_get_args());
        }catch(\ErrorException $ex){
            var_dump($ex);
        }
    }
}
class SystemWrapperObject2
{
    public static function var_dump(...$args)
    {
        echo "Hit";
    }
}
class FakeSessionHandler2 implements \SessionHandlerInterface
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
