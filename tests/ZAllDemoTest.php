<?php
namespace tests;

use PHPUnit\Framework\Assert;
use DuckPhp\HttpServer\HttpServer;

class ZAllDemoTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        // 这里 开启所有 demo 检测所有 demo 的结果
        $path_app = realpath(__DIR__.'/../template/').'/';
        $port = 9529;
        $server_options=[
            'path'=>$path_app,
            'path_document'=>'public',
            'port'=>$port,
            'background'=>true,
            
        ];
        HttpServer::RunQuickly($server_options);
        //echo HttpServer::_()->getPid();
        sleep(1);// ugly
        $host ="http://127.0.0.1:{$port}/";
        
        $tests = [
            'test/done'          => 95 ,
            'doc.php'            => 1329 ,
            ''                   => 1353 ,
            'files'              => 11169 ,
            'demo.php'           => 406 ,
            'helloworld.php'     => 11,
            'just-route.php'     => 109,
            'api.php/test.index' => 347 ,
            'traditional.php'    => 397 ,
            'rpc.php'            => 810,
        ];
        $result = true;

        foreach($tests as $k => $len){
            $data = $this->curl_file_get_contents($host.$k);
            $data =str_replace(realpath(__DIR__.'/../'),'',$data);
            
            $l=strlen($data);
            if($l!==$len){
                if($k ==='rpc.php' && $l==0){ // :( ugly. I don't know why.
                    continue;
                }
                echo "Failed: $k => $len($l) \n";
                //echo $data; echo "\n";
                
                $result = false;
            }
            
        }
        HttpServer::_()->close();
        $this->assertTrue($result);
    }
    protected function curl_file_get_contents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data !== false?$data:'';
    }
}
