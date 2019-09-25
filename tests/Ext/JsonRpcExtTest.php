<?php 
namespace tests\DNMVCS\Ext;
use DNMVCS\Ext\JsonRpcExt;

class JsonRpcExtTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(JsonRpcExt::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(JsonRpcExt::class);
        $this->assertTrue(true);
        /*
        JsonRpcExt::G()->init($options=[], $context);
        JsonRpcExt::G()->getRealClass($object);
        JsonRpcExt::G()->Wrap($class);
        JsonRpcExt::G()->_Wrap($class);
        JsonRpcExt::G()->_autoload($class);
        JsonRpcExt::G()->callRpc($classname, $method, $arguments);
        JsonRpcExt::G()->onRpcCall(array $input);
        JsonRpcExt::G()->curl_file_get_contents($url, $post);
        JsonRpcExt::G()->prepare_token($ch);
        JsonRpcExt::G()->__call($method, $arguments);
        //*/
    }
}
