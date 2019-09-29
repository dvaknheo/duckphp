<?php
namespace tests\DNMVCS\Ext{

use DNMVCS\Ext\JsonRpcExt;
use DNMVCS\Core\HttpServer;
use TestService;
use JsonRpc\TestService as JS;
class JsonRpcExtTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(JsonRpcExt::class);
        $path_app=\GetClassTestPath(JsonRpcExt::class);
        $options=[
            'jsonrpc_namespace'=>'JsonRpc',
            'jsonrpc_backend'=>'http://127.0.0.1:9528/json_rpc',
            'jsonrpc_is_debug'=>true,
        ];
        JsonRpcExt::G()->onRpcCall([
            'id'=>TestService::class,
            'method'=>'foo',
            'params'=>[
                'OK'
            ],
        ]);
        JsonRpcExt::G()->init($options,null);
        $server_options=[
            'path'=>$path_app,
            'path_document'=>'',
            'port'=>9528,
            'background'=>true,
        ];
        HttpServer::RunQuickly($server_options);
        $data=TestService::G(JsonRpcExt::Wrap(TestService::class))->foo();
        JS::G()->foo();
        
        echo HttpServer::G()->getPid();
        var_dump($data);
        HttpServer::G()->close();
        
        \MyCodeCoverage::G()->end(JsonRpcExt::class);
        $this->assertTrue(true);
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
} // endnamespace  tests\DNMVCS\Ext
namespace
{

use DNMVCS\Core\SingletonEx;
class TestService
{
    use SingletonEx;
    public function foo()
    {
        return 'Client:'.DATE(DATE_ATOM);
    }
}
} //endnamespace \