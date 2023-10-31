<?php
namespace tests\DuckPhp\Ext{
use DuckPhp\Core\SingletonTrait as SingletonExTrait;

use DuckPhp\Ext\JsonRpcExt;
use DuckPhp\HttpServer\HttpServer;
use TestService;
use JsonRpc\TestService as JS;
class JsonRpcExtTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(JsonRpcExt::class);
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(JsonRpcExt::class);
        
        $ret=JsonRpcExt::_()->onRpcCall([
            'method'=>TestService::class.'.foo',
            'params'=>[
                'OK'
            ],
        ]);
        $ret=JsonRpcExt::_()->onRpcCall([
            'method'=>'NoClass',
            'params'=>[
                'OK'
            ],
        ]);
        $ret=JsonRpcExt::_(new JsonRpcExt())->init([
            'jsonrpc_service_interface' => testInterface::class,
            'jsonrpc_service_namespace' => __NAMESPACE__,
        ])->onRpcCall([
            'method'=>'TestService2.foo',
            'params'=>[
                'OK'
            ],
        ]);
        $ret=JsonRpcExt::_(new JsonRpcExt())->init([
            'jsonrpc_service_interface' => 'noexites'
        ])->onRpcCall([
            'method'=>'TestService2.foo',
            'params'=>[
                'OK'
            ],
        ]);
        
        
        $options=[
            'jsonrpc_namespace'=>'JsonRpc',
            'jsonrpc_backend'=>'http://127.0.0.1:9528/json_rpc',
            'jsonrpc_is_debug'=>true,
            'jsonrpc_check_token_handler'=>function($ch){ var_dump('OOK');}
        ];
        
        JsonRpcExt::_(new JsonRpcExt())->init($options,null);
        
        $flag=class_exists('do_not_exoits');
        
        $server_options=[
            'path'=>$path_app,
            'path_document'=>'',
            'port'=>9528,
            'background'=>true,
            
        ];
        HttpServer::RunQuickly($server_options);
        echo HttpServer::_()->getPid();
        sleep(1);// ugly
        
        $data=TestService::_(JsonRpcExt::Wrap(TestService::class))->foo();
        JsonRpcExt::_()->getRealClass(TestService::_());
        JS::_()->foo();
        
        JsonRpcExt::_()->clear();
        $options['jsonrpc_backend']=['http://localdomain.dev/json_rpc','127.0.0.1:9528'];
        JsonRpcExt::_()->init($options,null);
        JS::_()->foo();

        try{
            JS::_()->the500();
        }catch(\Exception $ex){
            echo $ex;
        }
        try{
            JS::_()->throwException();
        }catch(\Exception $ex){
            echo $ex;
        }
        
        $options['jsonrpc_check_token_handler']=null;
        JsonRpcExt::_()->init($options,null);
        JS::_()->foo();
        //JS::_()->foo();
        
        var_dump($data);
        HttpServer::_()->close();
        JsonRpcExt::_()->isInited();
        \LibCoverage\LibCoverage::End();
        /*
        JsonRpcExt::_()->init($options=[], $context);
        JsonRpcExt::_()->getRealClass($object);
        JsonRpcExt::_()->Wrap($class);
        JsonRpcExt::_()->_Wrap($class);
        JsonRpcExt::_()->_autoload($class);
        JsonRpcExt::_()->onRpcCall(array $input);
        JsonRpcExt::_()->curl_file_get_contents($url, $post);
        JsonRpcExt::_()->prepare_token($ch);
        JsonRpcExt::_()->__call($method, $arguments);
        //*/
    }
}

interface testInterface
{
    //
}

class TestService2 implements testInterface
{
    use SingletonExTrait;
    public function foo()
    {
        return 'Client:'.DATE(DATE_ATOM);
    }
}

} // endnamespace  tests\DuckPhp\Ext
namespace
{

use DuckPhp\Core\SingletonTrait as SingletonExTrait;
class TestService
{
    use SingletonExTrait;
    public function foo()
    {
        return 'Client:'.DATE(DATE_ATOM);
    }
}
}