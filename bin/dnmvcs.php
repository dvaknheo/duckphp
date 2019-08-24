#!/usr/bin/env php
<?php
$is_debug=true;
if($is_debug){
    $autoload_file=__DIR__.'/../autoload.php';
    require($autoload_file);
    
    $base_path=realpath(dirname(realpath(__FILE__)).'/../');
    $path=getcwd();
    
    $source= __DIR__ .'/../template';
    $dest  =__DIR__ .'/../build';
}else{

}
/////////////////
showWelcome();

$shortopts  = "";
$shortopts .= "f:";  // Required value
$shortopts .= "v::"; // Optional value
$shortopts .= "abc"; // These options do not accept values

$longopts  = array(
    "required:",     // Required value
    "optional::",    // Optional value
    "option",        // No value
    "opt",           // No value
);
$options = getopt($shortopts, $longopts);
var_dump($options);



C::DumpDir($source,$dest);





var_dump(DATE(DATE_ATOM));
return ;
function showWelcome()
{
    echo "Well Come to use DNMVCS , for more info , use --help \n";
echo <<<EOT

----
create  create  a skeleton-project
--prune-core
--prune-helper
--namespace <namespace>
--no-compose
--start the project
----

EOT;
}


class C
{
    public static function DumpDir($source, $dest)
    {
        $source=realpath($source);
        $dest=realpath($dest);
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        foreach ($files as $file) {
            $short_file_name=substr($file, strlen($source)+1);
            if ($short_file_name==='headfile/headfile.php') {
                continue;
            }
            if ($short_file_name==='config/setting.php') {
                continue;
            }
            
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
            $data=self::Filte($data);
            file_put_contents($dest_file,$data);
            //decoct(fileperms($file) & 0777);
        }
    }
    public static function changeHeadFile($data)
    {
        $str_header="require(__DIR__.'/../vendor/autoload.php');"
        $data=str_replace("require(__DIR__.'/../headfile/headfile.php');",$str_header,$data);
        return $data;
    }
    public static function purceCore($data)
    {
        $data=str_replace("DNMVCS\\","DNMVCS\\Core\\",$data);
        $data=str_replace("DNMVCS\\Core\\DNMVCS","DNMVCS\\Core\\App",$data);
        $data=str_replace("DNMVCS\\Core\\Core","DNMVCS\\Core",$data);
        return $data;
    }
    public static function DeleteSomething($data)
    {
        $data=str_replace('//* DNMVCS TO DELETE ','/* DNMVCS Has DELETE ',$data);
        return $data;
    }
    public static function Filte($data)
    {
        if($namespace!=='MY'){
            $data=str_replace('MY\\',$namespace.'\\',$data);
        }
        return $data;
    }
}
