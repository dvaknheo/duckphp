<?php
namespace DNMVCS;

use DNMVCS\Core\HttpServer as Server;
use DNMVCS\SwooleHttpd;

class HttpServer extends Server
{
    protected $cli_options_ex=[
            'no-swoole'=>[
                'desc'=>'do not use swoole httpserver',
            ],
    ];
    protected function checkSwoole()
    {
        if (!function_exists('swoole_version')) {
            return false;
        }
        if (!class_exists(SwooleHttpd::class)) {
            return false;
        }
        return true;
    }
    protected function runHttpServer()
    {
        if (isset($this->args['no-swoole'])) {
            return parent::runHttpServer();
        }
        return $this->runSwooleServer($this->options['path'], $this->host, $this->port);
    }
    protected function runSwooleServer($path, $host, $port)
    {
        echo "DNMVCS: RunServer by swooleserver $host:$port\n";
        
        $dn_options=$this->options['dnmvcs']??[];
        $dn_options['path']=$path;
        $dn_options['swoole']=$dn_options['swoole']??[];
        $dn_options['swoole']['host']=$host;
        $dn_options['swoole']['port']=$port;

        if (defined('DNMVCS_WARNING_IN_TEMPLATE')) {
            $dn_options['skip_setting_file']=true;
            echo "Don't run the template file directly \n";
        }
        DNMVCS::RunQuickly($dn_options);
    }
}
