<?php
class MyCodeCoverage
{
    public $options=[
		'namespace' => null,
		'path'=>null,
		'path_src'=>'src',
		'path_dump'=>'test_coveragedumps',
		'path_report'=>'test_reports',
		'path_data'=>'tests/data_for_tests',
    ];
	public $is_inited =true;
	
    protected $extFile=null;
    protected $coverage;
    protected $test_class;
    
    protected $enable = true;

    public static function G($object=null)
    {
        if (defined('__SINGLETONEX_REPALACER')) {
            $callback = __SINGLETONEX_REPALACER;
            return ($callback)(static::class, $object);
        }
        static $_instance;
        $_instance=$object?:($_instance??new static);
        return $_instance;
    }
    public function init(array $options, ?object $context = null)
    {
$c_args=[
    '--coverage-clover',
    '--coverage-crap4j',
    '--coverage-html',
    '--coverage-php',
    '--coverage-text',
];
$flag = array_reduce(
    $c_args,
    function($flag,$v){
        return $flag || in_array($v,$_SERVER['argv']);
    },
    false
);
if($flag) {
    $this->enable=false;
    return $this;
}
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
		
		$this->options['path']=$this->optionsp['path']?? realpath(__DIR__ .'/..').'/';
		$this->options['path_src'] = $this->getComponenetPathByKey('path_src');
		$this->options['path_dump'] = $this->getComponenetPathByKey('path_dump');
        $this->options['path_report'] = $this->getComponenetPathByKey('path_report');
        $this->options['path_data'] = $this->getComponenetPathByKey('path_data');
		
		if(!is_dir($this->options['path_dump'])){
			mkdir($this->options['path_dump']);
		}
		if(!is_dir($this->options['path_report'])){
			mkdir($this->options['path_report']);
		}
		$this->is_inited=true;
        return $this;
    }
    protected function getComponenetPathByKey($path_key)
    {
        if (substr($this->options[$path_key], 0, 1) === '/') {
            return rtrim($this->options[$path_key], '/').'/';
        } else {
            return $this->options['path'].rtrim($this->options[$path_key], '/').'/';
        }
    }
    public function isInited():bool
    {
        return $this->is_inited;
    }
    public static function GetClassTestPath($class)
    {
        return static::G()->doGetClassTestPath($class);
    }
    public function doGetClassTestPath($class)
    {
        $blocks=explode('\\',$this->test_class);
        $root=array_shift($blocks);
        $this->options['namespace']=$this->options['namespace'] ?? $root;
        $ret=$this->options['path_data'].str_replace([$this->options['namespace'].'\\','\\'],['/','/'],$class).'/';
        return $ret;
    }
    protected static function include_file($file)
    {
        return include $file;
    }
    public function createReport()
    {
        $coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();
        $coverage->filter()->addDirectoryToWhitelist($this->options['path_src']);
        $coverage->setTests([
          'T' =>[
            'size' => 'unknown',
            'status' => -1,
          ],
        ]);
        $directory = new \RecursiveDirectoryIterator($this->options['path_dump'], \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);

        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        foreach ($files as $file) {
            $coverage->merge(static::include_file($file));
        }
        $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade;
        $writer->process($coverage, $this->options['path_report']);
        
        $report = $coverage->getReport();
        $lines_tested = $report->getNumExecutedLines();
        $lines_total = $report->getNumExecutableLines();
        $lines_percent = sprintf('%0.2f%%',$lines_tested/$lines_total *100);
        return [
            'lines_tested'=>$lines_tested,
            'lines_total'=>$lines_total,
            'lines_percent'=>$lines_percent,
        ];
    }


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
    public function prepareAttachFile($extFile)
    {
        $this->extFile=$extFile;
    }
    public function begin($class)
    {
        if(!$this->enable){
            return;
        }
        $this->test_class=$class;
        if(!$this->isInited()){
            $this->init([]);
        }
        $this->coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();
        $this->setPath($this->classToPath($class));
        if($this->extFile){
            $this->coverage->filter()->addFileToWhitelist($this->extFile);
        }
        $this->coverage->start($class);
    }
    public function end()
    {
        if(!$this->enable){
            return;
        }
        $this->coverage->stop();
        $writer = new \SebastianBergmann\CodeCoverage\Report\PHP;
        
        $blocks=explode('\\',$this->test_class);
        $root=array_shift($blocks);
        $this->options['namespace']=$this->options['namespace'] ?? $root;
        $path=substr(str_replace('\\', '/', $this->test_class), strlen($this->options['namespace'].'\\'));
        $path=realpath($this->options['path_dump']).'/'.$path .'.php';
        $writer->process($this->coverage, $path);
        
        $this->coverage=null;
        
        $this->showResult();
    }
    protected function showResult()
    {
        echo "\n\033[42;30m".$this->test_class."\033[0m Test Done!";
        \PHPUnit\Framework\Assert::assertTrue(true);
        echo "\n";
    }
    public function showAllReport()
    {
        $data = $this->createReport();
        echo "\nSTART CREATE REPORT AT " .DATE(DATE_ATOM)."\n";
        echo "File:\nfile://".$this->options['path_report']."index.html" ."\n"; 
                echo "\n\033[42;30m All Done \033[0m Test Done!";

        echo "\nTest Lines: \033[42;30m{$data['lines_tested']}/{$data['lines_total']}({$data['lines_percent']})\033[0m\n";
        echo "\n\n";
    }
    ////
    public function cleanDirectory($dir)
    {
        $dir = rtrim($dir,'/');
        $result = false;
        if ($handle = opendir("$dir")){
            $result = true;
            while ((($file=readdir($handle))!==false) && ($result)){
                if ($file!='.' && $file!='..'){
                    if($file==='.gitignore'){
                        continue;
                    }
                    if (is_dir("$dir/$file")){
                        $result = $this->cleanDirectory("$dir/$file");
                    } else {
                        $result = unlink("$dir/$file");
                    }
                }
            }
            closedir($handle);
            if ($result){
                $result = @rmdir($dir);
            }
        }
        return $result;
    }
    ///////////////////////
    
    public static function SimpleCover($src,$dest)
    {
        //
        $coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();    
        $coverage->filter()->addDirectoryToWhitelist($src);
        $coverage->start(DATE(DATE_ATOM));
        register_shutdown_function(function()use($coverage, $dest){
            $coverage->stop();
            $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade;
            $writer->process($coverage,$dest);
        });
    }

    public function DoTestFileGeneratorRun($source,$dest)
    {
        //先放这里
        return TestFileGenerator::Run($source, $dest);
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