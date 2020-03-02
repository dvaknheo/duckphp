<?php
require __DIR__ . '/../autoload.php';


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



return;
//*/
//-----------------------------------------------

function GetClassTestPath($class)
{
    $ret=__DIR__.'/data_for_tests'.str_replace(['DuckPhp\\','\\'],['/','/'],$class).'/';
    return $ret;
}


class MyCodeCoverage
{
    protected static function include_file($file)
    {
        return include $file;
    }
    public function createReport()
    {
        $path=realpath(__DIR__.'/../src');
        $coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();
        $coverage->filter()->addDirectoryToWhitelist($path);

        $coverage->setTests(array(
          'T' =>
          array(
            'size' => 'unknown',
            'status' => -1,
          ),
        ));

        $source=realpath(__DIR__.'/test_coveragedumps');
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);

        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        foreach ($files as $file) {
            $coverage->merge(static::include_file($file));
        }
        $coverage->filter()->removeDirectoryFromWhitelist($path.'/SwooleHttpd');
        $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade;
        $writer->process($coverage, __DIR__ . '/test_reports');
    }
    public static function G($object=null)
    {
        //Simplist
        static $_instance;
        $_instance=$object?:($_instance??new static);
        return $_instance;
    }
    protected $coverage;
    protected $test_class;
    protected function setPath($path)
    {
        if (is_file($path)) {
            $this->coverage->filter()->addFileToWhitelist($path);
        } elseif (is_object($path)) {
            $this->coverage->setFileter($path);
        } else {
            $this->coverage->filter()->addDirectoryToWhitelist($path);
        }
    }
    public function classToPath($class)
    {
        $ref=new ReflectionClass($class);
        return $ref->getFileName();
    }
    public function begin($class, $name='T')
    {
        $this->test_class=$class;
        $this->coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();
        $this->setPath($this->classToPath($class));
        $this->coverage->start($name);
    }
    public function end()
    {
        $this->coverage->stop();
        
        $writer = new \SebastianBergmann\CodeCoverage\Report\PHP;
        $path=substr(str_replace('\\', '/', $this->test_class), strlen('DuckPhp\\'));
        $path=__DIR__.'/test_coveragedumps/'.$path .'.php';
        $writer->process($this->coverage, $path);
        $this->coverage=null;
        $this->test_class='';
    }
    
    ///////////////////////
    public function run($path, $name, $callback)
    {
        $this->begin($path, $name);
        ($callback)($path, $name);
        return $this->end();
    }
    public function merge($path, $name, $data_list)
    {
        $coverage =$this->coverage??new \SebastianBergmann\CodeCoverage\CodeCoverage;
        $this->setPath($path);
        foreach ($data_list as $data) {
            $this->coverage->append($data, $name);
        }
    }
    public function reportHtml($output_path)
    {
        $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade;
        $writer->process($this->coverage, $output_path);
    }
    public function report($output_path)
    {
        /*
        $report = $this->coverage->getReport();
        $t=$report->getClasses();
        $ret=array_shift($t);
        unset($ret['methods']);
        var_dump( $ret );

        return;
        */
        $writer = new \SebastianBergmann\CodeCoverage\Report\Text;
        $x=$writer->process($this->coverage, $output_path);
        echo $x;
    }
    public function clear()
    {
        $this->coverage=null;
    }
}
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
            if (substr($short_file, 0, strlen('SwooleHttpd'))==='SwooleHttpd') {
                continue;
            }
            
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