<?php
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class HttpServer
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
                    'required'=>true,
                ],
                'port'=>[
                    'short'=>'P',
                    'desc'=>'set server port,default is 8080',
                    'required'=>true,
                ],
                'inner-server'=>[
                    'short'=>'i',
                    'desc'=>'use inner server',
                ],
                'docroot'=>[
                    'short'=>'t',
                    'desc'=>'document root',
                    'required'=>true,
                ],
                'file'=>[
                    'short'=>'f',
                    'desc'=>'index file',
                    'required'=>true,
                ],
                'dry'=>[
                    'desc'=>'dry mode, just show cmd',
                ],
            ],
        ];
    const DEFAULT_OPTIONS_EX=[
        ];
    
    //TODO API 自动类，
    //TODO 自动填充 Service 类
    
    protected $args=[];
    protected $docroot='';
    
    public static function RunQuickly($options)
    {
        static::G()->init($options)->run();
    }
    public function init($options=[], $context=null)
    {
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, static::DEFAULT_OPTIONS_EX, $options);
        $this->options=$options;
        
        $this->host=$options['host'];
        $this->port=$options['port'];
        $this->docroot=$options['docroot'];
        $this->args=$this->parseCaptures($options);
        
        $this->host=$this->args['host']??$this->host;
        $this->port=$this->args['port']??$this->port;
        $this->docroot=$this->args['docroot']??$this->docroot;
        return $this;
    }
    protected function parseCaptures($options)
    {
        foreach($options['args'] as $k=>$v){
            $required=$v['required']??false;
            $optional=$v['optional']??false;
            $a[]=$k.($required?':':'').($optional?'::':'');
            if(isset($v['short'])){
                $shorts[]=$v['short'].($required?':':'').($optional?'::':'');
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
    public function run()
    {
        $this->showWelcome();
        
        if (isset($this->args['help'])) {
            return $this->showHelp();
        }
        $this->runHttpServer();
    }
    protected function showWelcome()
    {
        echo "Well Come to use DNMVCS , for more info , use --help \n";
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
        $doc="Usage :\n\n";
        echo $doc;
        foreach($this->options['args'] as $k => $v){
            $long=$k;
            
            $t=$v['short']??'';
            $t=$t?'-'.$t:'';
            if($v['optional']??false){
                $long.=' ['.$k.']';
                $t.=' ['.$k.']';
            }
            if($v['required']??false){
                $long.=' <'.$k.'>';
                $t.=' <'.$k.'>';
            }
            echo " --{$long}\t{$t}\n\t".$v['desc']."\n";
        }
        echo "Current args :\n";
        var_export($this->args);
        echo "\n";
    }
    protected function runHttpServer()
    {
        $PHP=$_SERVER['_'];
        $PHP=escapeshellcmd($PHP);
        $host=escapeshellcmd($this->host);
        $port=escapeshellcmd($this->port);
        $document_root=escapeshellcmd($this->docroot);
       
        echo "DNMVCS: RunServer by php inner http server $host:$port\n";
        
        $cmd="$PHP -S $host:$port -t $document_root ";
        if(isset($this->args['dry'])){
            echo $cmd;
            echo "\n";
            return;
        }
        exec($cmd);
        
    }
}