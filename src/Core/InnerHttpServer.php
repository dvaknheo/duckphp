<?php
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class InnerHttpServer
{
    use SingletonEx;
    
    const DEFAULT_OPTIONS=[
            'host'=>'127.0.0.1',
            'port'=>'9527',
            'path'=>null,
            'args'=>[
                'help'=>[
                    'short'=>'h',
                    'desc'=>'show this help;',
                ],
                'host'=>[
                    'short'=>'H',
                    'desc'=>'set server host,default is 127.0.0.1',
                    'optional'=>true,
                ],
                'port'=>[
                    'short'=>'P',
                    'desc'=>'set server port,default is 8080',
                    'optional'=>true,
                ],
                'inner-server'=>[
                    'short'=>'i',
                    'desc'=>'use inner server',
                ],
            ],
        ];
    const DEFAULT_OPTIONS_EX=[
        ];
    
    //TODO API 自动类，
    //TODO 自动填充 Service 类
    
    protected $path_document='public';
    protected $args=[];
    protected $document_root='';
    
    public static function RunQuickly($host,$port,$path)
    {
        $options=[
            'host'=>$host,
            'port'=>$port,
            'path'=>$path,
        ];
        static::G()->init($options)->run($host,$port,$path);
    }
    public function init($options=[], $context=null)
    {
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, static::DEFAULT_OPTIONS_EX, $options);
        $this->options=$options;
        
        $this->host=$options['host'];
        $this->port=$options['port'];
        
        $this->args=$this->parseCaptures($options);
        
        $this->host=$this->args['host']??$this->host;
        $this->port=$this->args['port']??$this->port;
        
        return $this;
    }
    protected function parseCaptures($options)
    {
        foreach($options['args'] as $k=>$v){
            $optional=$v['optional']??false;
            $required=$v['required']??false;
            $a[]=$k.($optional?':':'').($required?'::':'');
            if(isset($v['short'])){
                $shorts[]=$v['short'].($optional?':':'').($required?'::':'');
                $shorts_map[$v['short']]=$k;
            }
            
        }
        $optind=null;
        $args=getopt(implode('', ($shorts)),$a, $optind);
        $args=$args?:[];
        
        $pos_args = array_slice($_SERVER['argv'], $optind);
        
        foreach($shorts_map as $k =>$v){
            if(isset($args[$k]) && !isset($args[$v])){
                $args[$v]=$args[$k];
            }
        }
        $args=array_merge($args,$pos_args);
        return $args;
    }
    public function run($host,$port,$path)
    {
        $this->showWelcome();
        
        if (isset($this->args['help'])) {
            return $this->showHelp();
        }
        $this->runHttpServer();
    }
    protected function showWelcome()
    {
        echo "Well Come to use DNMVCS ,for more info , use --help \n";
    }
    protected function getCmdCaptures()
    {
        $opts=[];
        
        $optind=null;
        $args=getopt($shorts,$a, $optind);
        $pos_args = array_slice($_SERVER['argv'], $optind);
        
        return $ret;
    }
    protected function showHelp()
    {
        $doc=<<<EOT
DNMVCS InnerHttpServer usage:

EOT;
foreach($this->options['args'] as $k => $v){
    $t=$v['short']??'';
    $t=$t?'-'.$t:'';
    echo " --{$k}\t{$t}\n\t".$v['desc']."\n";
}
        echo $doc;
    }
    protected function runHttpServer()
    {
        $PHP=$_SERVER['_'];
        $PHP=escapeshellcmd($PHP);
        $host=escapeshellcmd($this->host);
        $port=escapeshellcmd($this->port);
        $document_root=escapeshellcmd($this->document_root);
       
        echo "DNMVCS: RunServer by php inner http server $host:$port\n";
        $cmd="$PHP -S $host:$port -t $document_root ";
        echo $cmd;return;
        exec($cmd);
        
    }
}