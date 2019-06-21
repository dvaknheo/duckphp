<?php
namespace DNMVCS;

class InnerHttpServer
{
    public static function RunQuickly($host,$port,$path)
    {
        $class=new InnerHttpServer();
        $class->run($host,$port,$path);
    }
    public function run($host,$port,$path)
    {
        echo "Well Come to use DNMVCS ,for more info , use --help \n";
        $opts=[
            'help'	=>'h',
            'host:'	=>'H:',
            'port:'	=>'P:',
            'inner-server'=>'i',
        ];
        $captures=$this->getCmdCaptures($opts);
        $host=$captures['host']??$host;
        $port=$captures['port']??$port;
        if (isset($captures['help'])) {
            return $this->showHelp();
        }
        if (!$this->checkSwoole($captures)) {
            return $this->runHttpServer($path, $host, $port);
        }
        return $this->runSwooleServer($path, $host, $port);
    }
    protected function getCmdCaptures($opts)
    {
        $optind=null;
        $args=getopt(implode('', array_values($opts)), array_keys($opts), $optind);

        $shorts=array_map(function ($v) {
            return trim($v, ':');
        }, array_values($opts));
        $longs=array_map(function ($v) {
            return trim($v, ':');
        }, array_keys($opts));
        $new_opts=array_combine($shorts, $longs);
        $ret=[];
        foreach ($args as $k=>$v) {
            $key=$new_opts[$k]??$k;
            $ret[$key]=$v;
        }
        return $ret;
    }
    protected function showHelp()
    {
        $doc=<<<EOT
DNMVCS server usage:
  -h --help   show this help;
  -s --swoole use swoole server;
  -H --host   set server host,default is '8080';
  -P --port   set server port,default is '0.0.0.0';

EOT;
        echo $doc;
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
        if (!class_exists(SwooleHttpd\SwooleHttpd::class)) {
            return false;
        }
        return true;
    }
    protected function runHttpServer($path, $host, $port)
    {
        $PHP=$_SERVER['_'];
        $dir=$path.'public';
        $PHP=escapeshellcmd($PHP);
        $host=escapeshellcmd($host);
        $port=escapeshellcmd($port);
        echo "DNMVCS: RunServer by php inner http server $host:$port\n";
        $cmd="$PHP -t $dir -S $host:$port";
        exec($cmd);
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
        echo "DNMVCS: RunServer swoole server $host:$port\n";
        DNMVCS::RunQuickly($dn_options);
    }
    protected function getConfig()
    {
        return @include('start_server.config.php');
    }

}