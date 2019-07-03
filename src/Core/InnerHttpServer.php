<?php
namespace DNMVCS;

use DNMVCS\Core\SingletonEx;

class InnerHttpServer
{
    use SingletonEx;

    protected $path_document='public';

    public static function RunQuickly($host,$port,$path)
    {
        static::G()->run($host,$port,$path);
    }
    public function init($options=[], $context=null)
    {
        return $this;
    }
    

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
    protected function showWelcome()
    {
        echo "Well Come to use DNMVCS ,for more info , use --help \n";
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
DNMVCS InnerHttpServer usage:
  -h --help   show this help;
  -s --swoole use swoole server;
  -H --host   set server host,default is '8080';
  -P --port   set server port,default is '0.0.0.0';

EOT;
        echo $doc;
    }
    protected function runHttpServer($path, $host, $port)
    {
        $document_root=$path.$this->path_document;
        
        $PHP=$_SERVER['_'];
        $document_root=escapeshellcmd($document_root);
        $PHP=escapeshellcmd($PHP);
        $host=escapeshellcmd($host);
        $port=escapeshellcmd($port);
       
        $cmd="$PHP -t $dir -S $host:$port";
        
        echo "DNMVCS: RunServer by php inner http server $host:$port\n";
        
        exec($cmd);
    }
}