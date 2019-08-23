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
create  create seklton-project
start the project
route
----

EOT;
}


class C
{
    public static function DumpDir($source, $dest)
    {
        $source=realpath($source);
        $dest=realpath($dest);
        var_dump($source,$dest);
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
            copy($file, $dest.DIRECTORY_SEPARATOR.$short_file_name);
        }
    }
    protected static function ChangeFlag($file)
    {
        $data=file_get_contents($file);
        $data=str_replace('/headfile/headfile.php', '/vendor/autoload.php', $data);
        $data=str_replace("if(defined('DNMVCS_WARNING_IN_TEMPLATE'))", "// if(defined('DNMVCS_WARNING_IN_TEMPLATE'))", $data);
        
        file_put_contents($file, $data);
    }
    public static function DumpTemplateFiles()
    {
        return;
        $source=__DIR__.DIRECTORY_SEPARATOR.'/../template';
        $dest=getcwd();
        self::DumpDir($source, $dest);
        self::ChangeFlag('public/index.php');
        self::ChangeFlag('bin/start_server.php');
        copy('config/setting.sample.php', 'config/setting.php');
        $data="DNMVCS Installed at ".DATE(DATE_ATOM)."\n";
        file_put_contents('dnmvcs-installed.lock', $data);
    }
}

return;