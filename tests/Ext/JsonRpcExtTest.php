<?php
namespace tests\DuckPhp\Ext{
use DuckPhp\SingletonEx\SingletonExTrait;

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
        
        $ret=JsonRpcExt::G()->onRpcCall([
            'method'=>TestService::class.'.foo',
            'params'=>[
                'OK'
            ],
        ]);
        $ret=JsonRpcExt::G()->onRpcCall([
            'method'=>'NoClass',
            'params'=>[
                'OK'
            ],
        ]);
        $ret=JsonRpcExt::G(new JsonRpcExt())->init([
            'jsonrpc_service_interface' => testInterface::class,
            'jsonrpc_service_namespace' => __NAMESPACE__,
        ])->onRpcCall([
            'method'=>'TestService2.foo',
            'params'=>[
                'OK'
            ],
        ]);
        $ret=JsonRpcExt::G(new JsonRpcExt())->init([
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
        
        JsonRpcExt::G(new JsonRpcExt())->init($options,null);
        
        $flag=class_exists('do_not_exoits');
        
        $server_options=[
            'path'=>$path_app,
            'path_document'=>'',
            'port'=>9528,
            'background'=>true,
            
        ];
        HttpServer::RunQuickly($server_options);
        echo HttpServer::G()->getPid();
        sleep(1);// ugly
        
        $data=TestService::G(JsonRpcExt::Wrap(TestService::class))->foo();
        JsonRpcExt::G()->getRealClass(TestService::G());
        JS::G()->foo();
        
        JsonRpcExt::G()->clear();
        $options['jsonrpc_backend']=['http://localdomain.dev/json_rpc','127.0.0.1:9528'];
        JsonRpcExt::G()->init($options,null);
        JS::G()->foo();

        try{
            JS::G()->the500();
        }catch(\Exception $ex){
            echo $ex;
        }
        try{
            JS::G()->throwException();
        }catch(\Exception $ex){
            echo $ex;
        }
        
        $options['jsonrpc_check_token_handler']=null;
        JsonRpcExt::G()->init($options,null);
        JS::G()->foo();
        //JS::G()->foo();
        
        var_dump($data);
        HttpServer::G()->close();
        JsonRpcExt::G()->isInited();
        \LibCoverage\LibCoverage::End();
        /*
        JsonRpcExt::G()->init($options=[], $context);
        JsonRpcExt::G()->getRealClass($object);
        JsonRpcExt::G()->Wrap($class);
        JsonRpcExt::G()->_Wrap($class);
        JsonRpcExt::G()->_autoload($class);
        JsonRpcExt::G()->onRpcCall(array $input);
        JsonRpcExt::G()->curl_file_get_contents($url, $post);
        JsonRpcExt::G()->prepare_token($ch);
        JsonRpcExt::G()->__call($method, $arguments);
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

use DuckPhp\SingletonEx\SingletonExTrait;
class TestService
{
    use SingletonExTrait;
    public function foo()
    {
        return 'Client:'.DATE(DATE_ATOM);
    }
}
}