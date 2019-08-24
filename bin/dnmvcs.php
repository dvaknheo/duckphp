#!/usr/bin/env php
<?php
// bin/dnmvcs.php --create --namespace MyProject --prune-core --prune-helper --dest build --autoload-file ../autoload.php  --start

$is_debug=true;

if ($is_debug) {
    $autoload_file=__DIR__.'/../autoload.php';
    require($autoload_file);
}
/////////////////

$longopts  = array(
    "help",
    "create",
    "start",
    "prune-core",
    "prune-helper",
    
    "namespace:",
    "dest:",
    "autoload-file:",
);
$cli_options = getopt('', $longopts);
$options=[];
$options['help']=isset($cli_options['help'])?true:false;
$options['create']=isset($cli_options['create'])?true:false;
$options['start']=isset($cli_options['start'])?true:false;
$options['prune_helper']=isset($cli_options['prune-helper'])?true:false;
$options['prune_core']=isset($cli_options['prune-core'])?true:false;

$options['namespace']=isset($cli_options['namespace'])?$cli_options['namespace']:'';
$options['dest']=isset($cli_options['dest'])?$cli_options['dest']:'';
$options['autoload_file']=isset($cli_options['autoload-file'])?$cli_options['autoload-file']:'';

C::RunQuickly($options);
return ;



class C
{
    public $options=[
        'prune_helper'=>false,
        'prune_core'=>false,
        'namespace' =>'MY',
        'src'=>'',
        'dest'=>'',
    ];
    public function RunQuickly($options)
    {
        //$class=static::class;
        return (new static())->init($options)->run();
    }
    public function init($options)
    {
        $this->options=array_replace_recursive($this->options, $options);
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
            $dest=realpath($this->options['dest']);
            $this->dumpDir($source, $dest);
            
            $is_done=true;
        }
        if ($this->options['start']) {
        }
        if (!$is_done) {
            $this->showHelp();
            return;
        }
    }
    public function dumpDir($source, $dest)
    {
        $source=realpath($source);
        $dest=realpath($dest);
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        echo "Copying file...\n";
        foreach ($files as $file) {
            $short_file_name=substr($file, strlen($source)+1);
            if ($short_file_name=='headfile/headfile.php') {
                continue;
            }
            if ($this->options['prune_helper']) {
                if ($this->pruneHelper($short_file_name)) {
                    var_dump("skip $short_file_name");
                    continue;
                }
            }
            
            // mkdir.
            $blocks=explode(DIRECTORY_SEPARATOR, $short_file_name);
            array_pop($blocks);
            $full_dir=$dest;
            foreach ($blocks as $t) {
                $full_dir.=DIRECTORY_SEPARATOR.$t;
                if (!is_dir($full_dir)) {
                    mkdir($full_dir);
                }
            }
            
            $dest_file=$dest.DIRECTORY_SEPARATOR.$short_file_name;
            $data=file_get_contents($file);
            
            $data=$this->filteText($data);
            $data=$this->changeHeadFile($data);
            
            if ($this->options['prune_core']) {
                $data=$this->purceCore($data);
            }
            if ($this->options['namespace']) {
                $data=$this->filteNamespace($data);
            }
            ////
            file_put_contents($dest_file, $data);
            echo $dest_file;
            echo "\n";
            //decoct(fileperms($file) & 0777);
        }
    }
    protected function pruneHelper($short_file_name)
    {
        return false; //TODO  to work;
        if (substr($short_file_name, -strlen('Helper.php'))==='Helper.php') {
            return true;
        } else {
            return false;
        }
    }
    protected function filteText($data)
    {
        $data=str_replace('//* DNMVCS TO DELETE ', '/* DNMVCS HAS DELETE ', $data);
        $data=str_replace('/* DNMVCS TO KEEP ', '//* DNMVCS HAS KEEP ', $data);
        return $data;
    }
    protected function filteNamespace($data)
    {
        $namespace=$this->options['namespace'];
        if ($namespace==='MY') {
            return $data;
        }
        $data=str_replace('MY\\', $namespace.'\\', $data);
        return $data;
    }
    protected function changeHeadFile($data)
    {
        $autoload_file=$this->options['autoload_file']?$this->options['autoload_file']:"vendor/autoload.php";
        $str_header="require_once(__DIR__.'/../$autoload_file');";
        
        $data=str_replace("require_once(__DIR__.'/../headfile/headfile.php');", $str_header, $data);
        return $data;
    }
    protected function purceCore($data)
    {
        $data=str_replace("DNMVCS\\", "DNMVCS\\Core\\", $data);
        $data=str_replace("DNMVCS\\Core\\DNMVCS", "DNMVCS\\Core\\App", $data);
        $data=str_replace("DNMVCS\\Core\\Core", "DNMVCS\\Core", $data);
        return $data;
    }
    protected function showWelcome()
    {
        echo <<<EOT
Well Come to use DNMVCS Manager , for more info , use --help \n
EOT;
    }
    protected function showHelp()
    {
        echo <<<EOT
----
--help       Show this help.

--create     Create the skeleton-project
  --namespace <namespace>  Use another project namespace.
  --prune-core             Just use DNMVCS\Core ,but not use DNMVC\
  --prune-helper           Do not use the Helper class, 
  
  --autoload-file <path> use another autoload file.
  --dest [path] copy project file to here.

----
To start the project , use bin/start_server.php

EOT;
        //--start      Call the project start_server script. the project must has created.
        // --force-create
    }
}
