<?php
require __DIR__ . '/../autoload.php';
require __DIR__ . '/MyCodeCoverage.php';


$c_args=[
'--coverage-clover',
'--coverage-crap4j',
'--coverage-html',
'--coverage-php',
'--coverage-text',
];
$in_coverage=false;
foreach($c_args as $v){
    if(!in_array($v,$_SERVER['argv'])){ continue; }
    $in_coverage=true;
}
if(!$in_coverage && ini_get('tests.report')){
    register_shutdown_function(function(){
    echo "\n Generating Report \n";
        $dest=__DIR__.'/../tests';
        $source=__DIR__.'/../src';
        TestFileGenerator::Run($source,$dest);
    });
}

$options=[
	//
];

MyCodeCoverage::G()->init($options);

//生成文档
//RefFileGenerator::Run();
return;
//*/
//-----------------------------------------------


class TestFileGenerator
{
    public static function Run($source, $dest)
    {
        $source=realpath($source).'/';
        $dest=realpath($dest).'/';
        
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        foreach ($files as $file) {
            $short_file=substr($file, strlen($source));
            static::MakeDir($short_file, $dest);
            
            $data =static::MakeTest($file, $short_file);
            
            $file_name=$dest.str_replace('.php', 'Test.php', $short_file);
            if (is_file($file_name)) {
                echo "Skip Existed File:".$file_name."\n";
                continue;
            }
            file_put_contents($file_name, $data);
        }
    }
    protected static function MakeDir($short_file, $dest)
    {
        $blocks=explode(DIRECTORY_SEPARATOR, $short_file);
        array_pop($blocks);
        $full_dir=$dest;
        foreach ($blocks as $t) {
            $full_dir.=DIRECTORY_SEPARATOR.$t;
            if (!is_dir($full_dir)) {
                mkdir($full_dir);
            }
        }
    }
    protected static function MakeTest($file, $short_file)
    {
        $data=file_get_contents($file);
        preg_match_all('/ function (([^\(]+)\([^\)]*\))/', $data, $m);
        $funcs=$m[1];
        
        $ns='DuckPhp\\'.str_replace('/', '\\', dirname($short_file));
        $ns=str_replace('\.', '', $ns);
        if (dirname($short_file)=='.') {
            $namespace='tests';
        }
        $TestClass=basename($short_file, '.php').'Test';
        $InitClass=basename($short_file, '.php').'';
        
        $ret="<"."?php \n";
        $ret.=<<<EOT
namespace tests\\{$ns};
use {$ns}\\{$InitClass};

class $TestClass extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \\MyCodeCoverage::G()->begin({$InitClass}::class);
        
        //code here
        
        \\MyCodeCoverage::G()->end({$InitClass}::class);
        \$this->assertTrue(true);
        /*

EOT;
        foreach ($funcs as $v) {
            $v=str_replace(['&','callable '], ['',''], $v);
            $ret.=<<<EOT
        {$InitClass}::G()->$v;

EOT;
        }
        $ret.=<<<EOT
        //*/
    }
}

EOT;

        return $ret;
    }
}
class RefFileGenerator
{
    public static function Run()
    {
        $source=realpath(__DIR__.'/../src').'/';
        $dest=realpath(__DIR__.'/../doc/ref').'/';
        //$source=realpath($source).'/';
        //$dest=realpath($dest).'/';
        
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        $classes=[];
        foreach ($files as $file) {
            $short_file=substr($file, strlen($source));
            if(substr($short_file,0,1)==='.'){
                continue;
            }
            $class =str_replace(['/','.php'],['\\',''],$short_file);
            //$classes[]=$class;
            
            $file_name=$dest.str_replace(['/','.php'],['-','.md'],$short_file);
            if (is_file($file_name)) {
                echo "Skip Existed File:".$file_name."\n";
                continue;
            }
            $data=static::getTemplate($class);
            file_put_contents($file_name,$data);
        }
        /*
        $index_file=$dest.'index.md';
        if(is_file($index_file)){
            return;
        }
        $str="# Index\n ## Classes\n";
        foreach($classes as $class){
            $str.="* $class \n";
        }
        file_put_contents($index_file,$str);
        //*/
    }
    public static function genIndex()
    {
        $source=realpath(__DIR__.'/../src').'/';
        $dest=realpath(__DIR__.'/../doc/ref').'/';
        //$source=realpath($source).'/';
        //$dest=realpath($dest).'/';
        
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        $classes=[];
        foreach ($files as $file) {
            $short_file=substr($file, strlen($source));
            if(substr($short_file,0,1)==='.'){
                continue;
            }
            $class =str_replace(['/','.php'],['\\',''],$short_file);
            $classes[]=$class;
        }
        $str='';
        foreach($classes as $class){
            $file=str_replace('\\','-',$class).'.md';
            $str.="* [$class]({$file}) \n";
        }
        echo $str;
    }
    public static function getTemplate($class)
    {
        $ret=<<<'EOT'
# {ClassName}

## 简介

## 选项

## 公开方法


## 详解


EOT;
'EOT';
        $ret=str_replace('{ClassName}',$class,$ret);
        return $ret;
    }
}