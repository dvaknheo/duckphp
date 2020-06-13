<?php


class MyCodeCoverage
{
    public $options=[
    ];
    protected $extFile=null;
    protected $coverage;
    protected $test_class;
    protected $path_source;
    protected $path_dump;
    public $path_report;
    protected $namespace=null;
    public static function G($object=null)
    {
        //Simplist
        static $_instance;
        $_instance=$object?:($_instance??new static);
        return $_instance;
    }
    public function init(array $options, $context=null)
    {
        //return $this;
    }
    public function isInited():bool
    {
        return true;
    }
    public function __construct()
    {
        $this->path_source=realpath(__DIR__.'/../src');
        $this->path_dump=realpath(__DIR__.'/test_coveragedumps');
        $this->path_report=realpath(__DIR__.'/test_reports');
        $this->namespace=null;

    }
    public function GetClassTestPath($class)
    {
        return static::G()->doGetClassTestPath($class);
    }
    public function doGetClassTestPath($class)
    {
        $blocks=explode('\\',$this->test_class);
        $root=array_shift($blocks);
        $this->namespace=$this->namespace ?? $root;
        $ret=__DIR__.'/data_for_tests'.str_replace([$this->namespace.'\\','\\'],['/','/'],$class).'/';
        return $ret;
    }
    protected static function include_file($file)
    {
        return include $file;
    }
    public function createReport()
    {
        $coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();
        $coverage->filter()->addDirectoryToWhitelist($this->path_source);
        $coverage->setTests([
          'T' =>[
            'size' => 'unknown',
            'status' => -1,
          ],
        ]);

        $directory = new \RecursiveDirectoryIterator($this->path_dump, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);

        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        foreach ($files as $file) {
            $coverage->merge(static::include_file($file));
        }
        $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade;
        $writer->process($coverage, $this->path_report);
        
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
        $this->coverage->stop();
        $writer = new \SebastianBergmann\CodeCoverage\Report\PHP;
        
        $blocks=explode('\\',$this->test_class);
        $root=array_shift($blocks);
        $this->namespace=$this->namespace ?? $root;
        $path=substr(str_replace('\\', '/', $this->test_class), strlen($this->namespace.'\\'));
        $path=realpath($this->path_dump).'/'.$path .'.php';
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
    
    ///////////////////////
}
