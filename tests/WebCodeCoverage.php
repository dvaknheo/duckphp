<?php
use SebastianBergmann\CodeCoverage\CodeCoverage;
// 没使用，先放这里，这是一个用于增量覆盖测试 http 的类。
class WebCodeCoverage  // @codeCoverageIgnoreStart
{
    public $options=[
		'path'=>null,
		'path_src'=>'src',
		'path_dump'=>'test_coveragedumps',
		'path_report'=>'test_reports',
        'auto_report'=>true,
        'reg_shutdown'=>true,
    ];
	public $is_inited =true;
    protected $coverage;
    /////////////////////////
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
		
		$this->options['path'] = $this->options['path']?? realpath(__DIR__ .'/..').'/';
		$this->options['path_src'] = $this->getComponenetPathByKey('path_src');
		$this->options['path_dump'] = $this->getComponenetPathByKey('path_dump');
        $this->options['path_report'] = $this->getComponenetPathByKey('path_report');
		
		if(!is_dir($this->options['path_dump'])){
			mkdir($this->options['path_dump']);
		}
		if(!is_dir($this->options['path_report'])){
			mkdir($this->options['path_report']);
		}
        $this->rebuildFileCache();
		$this->is_inited = true;
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
    ////
    public function run()
    {
        if(!$this->isInited()){
            $this->init([]);
        }
        if($this->options['reg_shutdown']){
            register_shutdown_function([static::class,'OnShutDown']);
        }
        
        $name =$this->getName();
        $this->coverage = new CodeCoverage();
        $path=$this->options['path_src'];
        $this->coverage->filter()->addDirectoryToWhitelist($path);
        $this->coverage->start($name);
    }
    public static function OnShutDown()
    {
        return static::G()->_OnShutDown();
    }
    public function _OnShutDown()
    {
        $this->coverage->stop();
        $path=realpath($this->options['path_dump']).'/'.DATE('Ymd-His').'.json';
        $this->dump($this->coverage, $path);
    }

    public function report()
    {
        $coverage = new CodeCoverage();
        $coverage->filter()->addDirectoryToWhitelist($this->options['path_src']);
        $coverage->setTests([
          'T' =>[
            'size' => 'unknown',
            'status' => -1,
          ],
        ]);
        $files=$this->filterJsonFile($this->options['path_dump']);
        foreach ($files as $file) {
            $data=file_get_contents($file);
            $object=$this->createFromJson(json_decode($data,true));
            if(!$object){
                continue;
            }
            echo "Merge: $file \n";
            $coverage->merge($object);
        }
         echo "reporting...\n";
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
    //////////////////////////
    protected static function include_file($file)
    {
        return include $file;
    }
    protected function filterPhpFile($source)
    {
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::FOLLOW_SYMLINKS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $ret = new \RegexIterator ($iterator, '/\.php$/',\RegexIterator::MATCH);
        $ret = \array_values(\iterator_to_array($ret));
        return $ret;
    }
    protected function filterJsonFile($source)
    {
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::FOLLOW_SYMLINKS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $ret = new \RegexIterator ($iterator, '/\.json$/',\RegexIterator::MATCH);
        $ret = \array_values(\iterator_to_array($ret));
        return $ret;
    }
    ////
    protected function getRequestName()
    {
        return $_SERVER['REQUEST_URI'] ?? 'XX';
    }
    protected function getName()
    {
        return $_SERVER['REQUEST_URI'] ?? 'xx';
    }
    protected function dump(CodeCoverage $coverage, string $target)
    {
        $input=$coverage->getData(true);
        
        ////$this->save($coverage,$this->options['path_dump'].'in.php');
        $uuid_result=uniqid();
        $uuid_emptys=uniqid();
        $uuid_blanks=uniqid();
        $ret=[
            'request'=> $this->getRequestName(),
            'name' =>$this->getName(),
            'date'=>DATE(DATE_ATOM),
            'file_md5'=>[],
            'result'=>$uuid_result,
            'blanks'=>$uuid_blanks,
            'emptys'=>$uuid_emptys,
        ];
        $file_md5=[];
        $result=[];
        $blanks=[];
        $emptys=[];
        
        foreach($input as $fullfile=>$v){
            $file=substr($fullfile,strlen($this->options['path_src']));
            $md5=$this->getFileMd5($file);
            $file_md5[$md5]=$file;
            $result[$file]=[];
            $emptys[$file]=[];
            foreach($v as $line => $d){
                if(is_null($d)){
                    $emptys[$file][]=$line;
                    continue;
                }
                if(empty($d) && is_array($d)){
                    $blanks[$file][]=$line;
                    continue;
                }
                $result[$file][]=$line;
            }
        }
        $ret['file_md5']=$file_md5;
        

        $output =json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        $result =json_encode($result, JSON_NUMERIC_CHECK);
        $blanks =json_encode($blanks, JSON_NUMERIC_CHECK);
        $emptys =json_encode($emptys, JSON_NUMERIC_CHECK);
        
        $output=str_replace('"'.$uuid_result.'"',$result,$output);
        $output=str_replace('"'.$uuid_blanks.'"',$blanks,$output);
        $output=str_replace('"'.$uuid_emptys.'"',$emptys,$output);
        
        file_put_contents($target,$output);
        
        return $output;
    }
    protected function getFileMd5($file)
    {
        $ret=$this->file_md5[$file]??null;
        if($ret === null){
            return md5(@file_get_contents($fullfile));
        }
        return $ret;
    }
    protected function rebuildFileCache()
    {
        $cache_file=$this->options['path_dump'].'.md5_cache';
        $cache=@file_get_contents($cache_file);
        $cache=@json_decode($cache,true);
        $cache=$cache ?? [];
        $mtimes=$cache['mtime']??[];
        $md5s=$cache['md5']??[];
        $is_change = false;
        
        $files=$this->filterPhpFile($this->options['path_src']);
        
        foreach($files as $file){
            $date = DATE(DATE_ATOM,filemtime($file));
            $file=substr($file,strlen($this->options['path_src']));
            if(!isset($mtimes[$file]) || $date !=$mtimes[$file]){
                $md5s[$file]=md5(file_get_contents($this->options['path_src'].$file));
                $mtimes[$file]=$date;
                $is_change=true;
            }
        }
        if($is_change){
            $data = json_encode(['md5'=>$md5s, 'mtime'=>$mtimes],JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            file_put_contents($cache_file,$data);
        }
        $this->file_md5=$md5s;
    }
    protected function createFromJson($input,$force = false)
    {
        if($input === null){
            //var_dump("No Input");
            return null;
        }
        $name=$input['name'];
        $file_md5=$input['file_md5']??[];
        $t=array_flip($file_md5);
        $t=array_diff_assoc($t,$this->file_md5);
        if(!$force && !empty($t)){
            return null;
        }
        $data=[];
        
        foreach($input['result'] as $file => $v){
            $key=$this->options['path_src'].$file;
            $data[$key] = $data[$key] ?? [];
            
            // dot not  array_merge ,cause a bug.
            foreach($v as $t){
                $data[$key][$t]=[$name];
            }
        }
        foreach($input['blanks'] as $file => $v){
            $key=$this->options['path_src'].$file;
            $data[$key] = $data[$key] ?? [];
            
            // dot not  array_merge ,cause a bug.
            foreach($v as $t){
                $data[$key][$t]=[];
            }
        }
        //*
        foreach($input['emptys'] as $file => $v){
            $key=$this->options['path_src'].$file;
            $data[$key] = $data[$key] ?? [];
            foreach($v as $t){
                $data[$key][$t]=null;
            }
        }
        foreach($data as $k=> &$v){
            ksort($v);
        }
        unset($v);
        
        $coverage = new CodeCoverage();
        $coverage->setData($data);
        $coverage->setTests([
            $name =>[
                'size' => 'unknown',
                'status' => -1,
            ],
        ]);
        $filter = $coverage->filter();
        $filter->setWhitelistedFiles( array_fill_keys(array_keys($data),true) );
        
        /////////$this->save($coverage,$this->options['path_dump'].'out.php');
        return $coverage;
    }
    protected function save($coverage,$path)
    {
        $writer = new \SebastianBergmann\CodeCoverage\Report\PHP;
        $writer->process($coverage, $path);
    }
} // @codeCoverageIgnoreEnd