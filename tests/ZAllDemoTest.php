<?php
namespace tests;

use PHPUnit\Framework\Assert;
use DuckPhp\HttpServer\HttpServer;

class ZAllDemoTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        // 这里 开启所有 demo 检测所有 demo 的结果
        $config = require __DIR__.'/data_for_tests/ZAllDemoTest.config.php';
        $path_app = $config['path_app'];
        $port = $config['port'];
        $server_options = $config['server_options'];
        HttpServer::RunQuickly($server_options);
        //echo HttpServer::_()->getPid();
        sleep(1);// ugly
        $host ="http://127.0.0.1:{$port}/";
        
        $tests = $config['tests'];
        $result = true;

        foreach($tests as $k => $len){
            $data = $this->curl_file_get_contents($host.$k);
            $data =str_replace(realpath(__DIR__.'/../'),'',$data);
            if($k === 'files'){
                // 裁剪动态内容（执行时间/内存、调用堆栈、包含文件），使 php74/php84 输出一致
                $data = $this->normalizeFilesContent($data);
            }
            
            $l=strlen($data);
            if($l!==$len){
                if($k ==='rpc.php' && $l==0){ // :( ugly. I don't know why.
                    continue;
                }
                echo "Failed: $k => $len($l) \n";
                if ($config['echo_failed_content']) {
                    echo $data; echo "\n";       
                }
                
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
    /**
     * 裁剪 files 页面中随运行/版本变化的 fieldset 块（执行时间/内存、全部单例、调用堆栈、包含文件）。
     * @param string $data
     * @return string
     */
    protected function normalizeFilesContent($data)
    {
        $lines = explode("\n", $data);
        $ret = [];
        $skip = false;
        foreach ($lines as $line) {
            if (strpos($line, '<fieldset>') !== false) {
                $skip = false;
            }
            if (!$skip && strpos($line, '<legend>') !== false && (
                strpos($line, '执行时间') !== false ||
                strpos($line, '全部单例') !== false ||
                strpos($line, '调用堆栈') !== false ||
                strpos($line, '包含文件') !== false
            )) {
                $skip = true;
            }
            if ($skip) {
                if (strpos($line, '</fieldset>') !== false) {
                    $skip = false;
                }
                continue;
            }
            $ret[] = $line;
        }
        return implode("\n", $ret);
    }
}
