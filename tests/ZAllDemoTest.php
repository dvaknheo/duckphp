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
            'files'              => 10016 ,
            'demo.php'           => 406 ,
            'helloworld.php'     => 11,
            'just-route.php'     => 141,
            'api.php/test.index' => 339 ,
            'traditional.php'    => 397 ,
            'rpc.php'            => 743,
        ];
        $result = true;
        foreach($tests as $k => $len){
            $data = $this->curl_file_get_contents($host.$k);
            
            $data =str_replace(realpath(__DIR__.'/../'),'',$data);
            
            $l=strlen($data);
            if($l!==$len){
                echo "Failed: $k => $len($l) \n";
                $result = false;
            }
            
        }
        HttpServer::_()->close();
        $this->assertTrue($result);
    }
    protected function curl_file_get_contents($url, $post =[])
    {
        $ch = curl_init();
        
        if (is_array($url)) {
            list($base_url, $real_host) = $url;
            $url = $base_url;
            $host = parse_url($url, PHP_URL_HOST);
            $port = parse_url($url, PHP_URL_PORT);
            $c = $host.':'.$port.':'.$real_host;
            curl_setopt($ch, CURLOPT_CONNECT_TO, [$c]);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        if(!empty($post)){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
            //$this->prepare_token();
        }
        
        
        $data = curl_exec($ch);
        curl_close($ch);
        return $data !== false?$data:'';
    }
}
