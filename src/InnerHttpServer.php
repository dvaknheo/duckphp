<?php
namespace DNMVCS;

use DNMVCS\Core\InnerHttpServer as Server;
use DNMVCS\SwooleHttpd;

class InnerHttpServer extends Server
{
    public function run($host,$port,$path)
    {
        
        $opts=[
            'help'	=>'h',
            'host:'	=>'H:',
            'port:'	=>'P:',
            'inner-server'=>'i',
        ];
        $captures=$this->getCmdCaptures($opts);
        $host=$captures['host']??$host;
        $port=$captures['port']??$port;
        
        $this->showWelcome();
        if (isset($captures['help'])) {
            return $this->showHelp();
        }
        $this->runHttpServer($path, $host, $port);
    }
    protected function checkSwoole($args)
    {
        $flag=(isset($args['inner-server']))?true:false;
        if ($flag) {
            return false;
        }
        if (!function_exists('swoole_version')) {
            return false;
        }
        if (!class_exists(SwooleHttpd::class)) {
            return false;
        }
        return true;
    }
    protected function runHttpServer($path, $host, $port)
    {
        if ($this->checkSwoole($captures)) {
            return $this->runSwooleServer($path, $host, $port);
        }
        return parent::runHttpServer($path, $host, $port);
    }
    protected function runSwooleServer($path, $host, $port)
    {
        $dn_options=$this->getConfig();
        $dn_options=$dn_options??[];
        $dn_options['path']=$path;
        $dn_options['swoole']=$dn_options['swoole']??[];
        $dn_options['swoole']['host']=$dn_options['swoole']['host']??$host;
        $dn_options['swoole']['port']=$dn_options['swoole']['port']??$port;
        $host=$dn_options['swoole']['host'];
        $port=$dn_options['swoole']['port'];

        if (defined('DNMVCS_WARNING_IN_TEMPLATE')) {
            $dn_options['skip_setting_file']=true;
            echo "Don't run the template file directly \n";
        }
        $this->showRunInfo($host, $port);
        DNMVCS::RunQuickly($dn_options);
    }
    protected function getConfig()
    {
        return @include('start_server.config.php');
    }

}