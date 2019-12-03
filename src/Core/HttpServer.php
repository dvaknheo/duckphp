<?php declare(strict_types=1);
namespace DuckPhp\Core;

use DuckPhp\Core\SingletonEx;

class HttpServer
{
    use SingletonEx;
    
    public $options=[
            'host'=>'127.0.0.1',
            'port'=>'9527',
            'path'=>null,
            'path_document'=>'public',
        ];
    protected $cli_options=[
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
            'background'=>[
                'short'=>'b',
                'desc'=>'run background',
            ],
        ];
    public $pid=0;
    
    protected $cli_options_ex=[];
    protected $args=[];
    protected $docroot='';
    
    protected $host;
    protected $port;
    
    public function __construct()
    {
    }    
    public static function RunQuickly($options)
    {
        return static::G()->init($options)->run();
    }
    public function init(array $options, object $context=null)
    {
        $this->options=array_replace_recursive($this->options, $options);
        $this->host=$this->options['host'];
        $this->port=$this->options['port'];
        $this->args=$this->parseCaptures($this->cli_options);
        
        $this->docroot=rtrim($this->options['path']??'', '/').'/'.$this->options['path_document'];
        
        $this->host=$this->args['host']??$this->host;
        $this->port=$this->args['port']??$this->port;
        $this->docroot=$this->args['docroot']??$this->docroot;
        return $this;
    }
    protected function getopt($options, $longopts, &$optind)
    {
        return getopt($options, $longopts, $optind); // @codeCoverageIgnore
    }
    protected function parseCaptures($cli_options)
    {
        $shorts_map=[];
        $shorts=[];
        $longopts=[];
        
        foreach ($cli_options as $k=>$v) {
            $required=$v['required']??false;
            $optional=$v['optional']??false;
            $longopts[]=$k.($required?':':'').($optional?'::':'');
            if (isset($v['short'])) {
                $shorts[]=$v['short'].($required?':':'').($optional?'::':'');
                $shorts_map[$v['short']]=$k;
            }
        }
        $optind=null;
        $args=$this->getopt(implode('', ($shorts)), $longopts, $optind);
        $args=$args?:[];
        
        $pos_args = array_slice($_SERVER['argv'], $optind);
        
        foreach ($shorts_map as $k =>$v) {
            if (isset($args[$k]) && !isset($args[$v])) {
                $args[$v]=$args[$k];
            }
        }
        $args=array_merge($args, $pos_args);
        return $args;
    }
    public function run()
    {
        $this->showWelcome();
        if (isset($this->args['help'])) {
            return $this->showHelp();
        }
        return $this->runHttpServer();
    }
    public function getPid()
    {
        return $this->pid;
    }
    public function close()
    {
        if (!$this->pid) {
            return false;
        }
        posix_kill($this->pid, 9);
    }
    protected function showWelcome()
    {
        echo "DuckPhp: Wellcome, for more info , use --help \n";
    }
    protected function showHelp()
    {
        $doc="Usage :\n\n";
        echo $doc;
        foreach ($this->cli_options as $k => $v) {
            $long=$k;
            
            $t=$v['short']??'';
            $t=$t?'-'.$t:'';
            if ($v['optional']??false) {
                $long.=' ['.$k.']';
                $t.=' ['.$k.']';
            }
            if ($v['required']??false) {
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
        $PHP='/usr/bin/env php';
        $host=escapeshellcmd((string)$this->host);
        $port=escapeshellcmd((string)$this->port);
        $document_root=escapeshellcmd($this->docroot);
       
        if (isset($this->args['background'])) {
            $this->options['background']=true;
        }
        if ($this->options['background']??false) {
            echo "DuckPhp: RunServer by PHP inner http server $host:$port\n";
        }
        $cmd="$PHP -S $host:$port -t $document_root ";
        if (isset($this->args['dry'])) {
            echo $cmd;
            echo "\n";
            return;
        }
        if ($this->options['background']??false) {
            $cmd.= ' > /dev/null 2>&1 & echo $!; ';
            $pid=exec($cmd);
            $this->pid=(int)$pid;
            return $pid;
        }
        echo "DuckPhp running at : http://$host:$port/ \n";
        return exec($cmd); // @codeCoverageIgnore
    }
}
