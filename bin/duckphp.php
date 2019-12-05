<?php
// bin/duckphp.php --create --namespace MyProject --prune-core --prune-helper --dest build --autoload-file ../autoload.php  --start
/////////////////

$options=[
    // do noting;
];
Installer::RunQuickly($options);

return;

class Installer
{
    public function __construct()
    {
    }
    public $options=[
        'prune_helper'=>false,
        'prune_core'=>false,
        'namespace' =>'MY',
        'src'=>'',
        'dest'=>'',
        'run'=>false,
    ];
    public function RunQuickly($options)
    {
        return (new static())->init($options)->run();
    }
    public function init($options)
    {
        $cli_options=$this->getOptionsByCli();
        $this->options=array_replace_recursive($cli_options,$this->options, $options);
        return $this;
    }
    public function run()
    {
        $this->showWelcome();
        if ($this->options['help']) {
            $this->showHelp();
            return;
        }
        $is_done=false;
        if ($this->options['create']) {
            $source= __DIR__ .'/../template';
            $dest=$this->options['dest'];
            $this->dumpDir($source, $dest, $this->options['force'],$this->options['full']);
            
            $is_done=true;
        }
        if ($this->options['start']) {
            echo "----------------------\n";
            echo "Start Inner PHP Server\n";
            echo "----------------------\n";
            $dest=realpath($this->options['dest']);
            $file=$dest.'/bin/start_server.php';
            $PHP='/usr/bin/env php ';
            $file=escapeshellcmd($file);
            $cmd=$PHP.$file;
            $cmd.=!empty($this->options['host'])?' --host='.escapeshellcmd($this->options['host']):'';
            $cmd.=!empty($this->options['port'])?' --port='.escapeshellcmd($this->options['port']):'';
            
            system($cmd);
            return;
        }
        if (!$is_done) {
            $this->showHelp();
            return;
        }
    }
    protected function getOptionsByCli()
    {
        $longopts  = [
            "help",
            "start",
            "create",
            "force",
            "full",
            "prune-core",
            "prune-helper",
            
            "namespace:",
            "dest:",
            "autoload-file:",
            'host:',
            'port:',
        ];
        
        $cli_options = getopt('', $longopts);
        
        $options=[];
        $options['help']=isset($cli_options['help'])?true:false;
        $options['start']=isset($cli_options['start'])?true:false;
        $options['create']=isset($cli_options['create'])?true:false;
        $options['force']=isset($cli_options['force'])?true:false;
        $options['prune_helper']=isset($cli_options['prune-helper'])?true:false;
        $options['prune_core']=isset($cli_options['prune-core'])?true:false;
        $options['full']=isset($cli_options['full'])?true:false;


        $options['namespace']=isset($cli_options['namespace'])?$cli_options['namespace']:'';
        $options['dest']=isset($cli_options['dest'])?$cli_options['dest']:'';
        $options['autoload_file']=isset($cli_options['autoload-file'])?$cli_options['autoload-file']:'';
        $options['host']=isset($cli_options['host'])?$cli_options['host']:'';
        $options['port']=isset($cli_options['port'])?$cli_options['port']:'';
        
        return $options;
    }
    public function dumpDir($source, $dest, $force=false,$is_full=false)
    {
        $source=rtrim(realpath($source),'/').'/';
        $dest=rtrim(realpath($dest),'/').'/';
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $t_files = \iterator_to_array($iterator, false);
        $files=[];
        
        foreach ($t_files as $file) {
            $files[$file]=substr($file, strlen($source));
        }
        
        if(!$force){
            $flag=$this->checkFilesExist($source, $dest, $files);
            if(!$flag){
                return;
            }
        }
        echo "Copying file...\n";
        
        $flag=$this->createDirectories($dest, $files);
        if(!$flag){
            return;
        }
        
        foreach ($files as $file =>$short_file_name) {
            $is_in_full=substr($short_file_name,0,strlen('public/full/'))==='public/full/'?true:false;
            if(!$is_full && $is_in_full){
                continue;
            }
            /*
            if ($this->options['prune_helper']) {
                if ($this->pruneHelper($short_file_name)) {
                    echo "prune skip: $short_file_name \n";
                    continue;
                }
            }
            */
            
            $data=file_get_contents($file);
            
            $data=$this->filteText($data,$is_in_full,$short_file_name);
            
            $dest_file=$dest.$short_file_name;
            
            $flag=file_put_contents($dest_file, $data);
            
            echo $dest_file;
            echo "\n";
            //decoct(fileperms($file) & 0777);
        }
    }
    protected function checkFilesExist($source,$dest,$files)
    {
        foreach ($files as $file=> $short_file_name) {
            if(!$this->options['full']){
                if(substr($short_file_name,0,strlen('public/full/'))==='public/full/'){
                    continue;
                }
            }
            $dest_file=$dest.$short_file_name;
            if(is_file($dest_file)){
                echo "file exists: $dest_file \n";
                echo "use --force to overwrite existed files \n";
                return false;
            }
        }
        return true;
    }
    protected function createDirectories($dest,$files)
    {
        foreach ($files as $file => $short_file_name) {
            // mkdir.
            $blocks=explode('/', $short_file_name);
            array_pop($blocks);
            $full_dir=$dest;
            foreach ($blocks as $t) {
                $full_dir.=DIRECTORY_SEPARATOR.$t;
                if (!is_dir($full_dir)) {
                    $flag = mkdir($full_dir);
                    if(!$flag){
                        echo "create file failed $full_dir";
                        return false;
                    }
                }
            }
        }
        return true;
    }
    protected function filteText($data, $is_in_full,$short_file_name)
    {
        $data = $this->changeHeadFile($data, $short_file_name);
        
        if(!$is_in_full){
            $data=$this->filteMacro($data);
            if ($this->options['prune_core']) {
                $data=$this->purceCore($data);
            }
            if ($this->options['namespace']) {
                $data=$this->filteNamespace($data, $this->options['namespace']);
            }
        }
        return $data;
    }
    protected function filteMacro($data)
    {
        $data=preg_replace('/^.*?@DUCKPHP_DELETE.*?$/m', '', $data);
        
        return $data;    
    }
    protected function filteNamespace($data, $namespace)
    {
        if ($namespace==='MY') {
            return $data;
        }
        $data=str_replace('MY\\', $namespace.'\\', $data);
        return $data;
    }
    protected function changeHeadFile($data,$short_file_name)
    {
        $autoload_file=$this->options['autoload_file']?$this->options['autoload_file']:"vendor/autoload.php";
        $level=substr_count($short_file_name, '/');
        $subdir=str_repeat('../',$level);
        $str_header="require_once(__DIR__.'/{$subdir}$autoload_file');";
        
        $data=preg_replace('/^.*?@DUCKPHP_HEADFILE.*?$/m', $str_header, $data);
        
        return $data;
    }
    protected function purceCore($data)
    {
        $data=str_replace("DuckPhp\\", "DuckPhp\\Core\\", $data);
        $data=str_replace("DuckPhp\\Core\\Core", "DuckPhp\\Core", $data);
        return $data;
    }
    protected function pruneHelper($short_file_name)
    {
        return false; //TODO notwork ,to make it work ,should change
        if (substr($short_file_name, -strlen('Helper.php'))==='Helper.php') {
            return true;
        } else {
            return false;
        }
    }
    
    protected function showWelcome()
    {
        echo <<<EOT
Well Come to use DuckPhp Manager , for more info , use --help \n
EOT;
    }
    protected function showHelp()
    {
        echo <<<EOT
----
--help       Show this help.

--create     Create the skeleton-project
  --namespace <namespace>   Use another project namespace.
  --force                   Overwrite exited files.
  --full                    Use The demo template
  --prune-core              Just use DuckPhp\Core ,but not use DuckPhp
  
  --autoload-file <path>    Use another autoload file.
  --dest [path]             Copy project file to here.
--start                     Start the server var bin/start_server.php
  --host [host]             Use this host
  --port [port]             Use this port
----
To start the project , use '--start' or run script 'bin/start_server.php'

EOT;
//--prune-helper            Do not use the Helper class, 
    }
}
