<?php
namespace DNMVCS;

use DNMVCS\Core\HttpServer as Server;
use DNMVCS\SwooleHttpd;

class HttpServer extends Server
{
     const DEFAULT_OPTIONS_EX=[
            'args'=>[
                'no-swoole'=>[
                    'desc'=>'do not use swoole httpserver',
                ],
                ''
            ],
    ];
    protected function checkSwoole($args)
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
        if(isset($this->args['no-swoole'])){
            return parent::runHttpServer();
        }
        return $this->runSwooleServer($this->docroot, $this->host, $this->port);
    }
    protected function runSwooleServer($path, $host, $port)
    {
        //$this->showRunInfo($host, $port);
        $dn_options=[];
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