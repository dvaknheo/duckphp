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
	public static function GetTestSetting()
	{
		return static::G()->doGetTestSetting();
	}
	public function doGetTestSetting()
	{
		return include $this->options['path_data'].'setting.php';
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
    
    ///////////////////////
}
