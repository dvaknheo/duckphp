<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Core\App;
use DuckPhp\Core\Configer;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Foundation\SqlDumper;
use DuckPhp\Foundation\ThrowOnableTrait;

class Installer extends ComponentBase
{
    const NEED_DATABASE = -1;
    const NEED_INSTALL = -2;
    const NEED_OTHER = -3;
    const INSTALLLED = -4;
    
    use ThrowOnableTrait;
    
    public $options = [
        'install_force' => false,
        
        'install_search_table' => '',
        'install_table_prefix' => '',
        'install_tablle_replace_prefix' => true,
        'install_tables' =>[],
    ];
    public function __construct()
    {
        parent::__construct();
        $this->exception_class = InstallerException::class;
    }
    //
    public function isInstalled()
    {
        $path_lock = $this->getComponentPath(Configer::G()->options['path_config'],Configer::G()->options['path']);
        $namespace = ($this->context_class)::G()->plugin_options['plugin_namespace'] ?? (($this->context_class)::G()->options['namespace'] ?? 'unkown');
        $file = $path_lock . $namespace. '.installed';
        return is_file($file);
    }
    ////////////////
    public function checkInstall($context, $options,$has_database)
    {
        static::ThrowOn(!$has_database, '你需要外部配置，如数据库等', static::NEED_DATABASE);
        $flag = $this->init($options,$context)->isInstalled();
        static::ThrowOn(!$flag,"你需要安装",static::NEED_INSTALL);
    }
    //////////////////

    public function install()
    {
        $info = '';
        if(!$this->options['force'] && $this->isInstalled()){
           static::ThrowOn(true,'你已经安装 !', '');     
        }
        
        $this->initSqlDumper();
        
        try{
            $info = SqlDumper::G()->install($this->options['install_force']??false);
        }catch(\Exception $ex){
            static::ThrowOn(true, "写入数据库失败:" . $ex->getMessage(),-2);
        }
        if($info){
            return $info;
        }
        $flag = $this->writeLock();
        static::ThrowOn(!$flag,'写入锁文件失败', -3);
        
        return $info;
    }
    public function dumpSql()
    {
        return $this->initSqlDumper()->run();
    }
    /////////////////////////////
    protected function writeLock()
    {
        $path_lock = $this->getComponentPath(Configer::G()->options['path_config'],Configer::G()->options['path']);
        $namespace = ($this->context_class)::G()->plugin_options['plugin_namespace'] ?? (($this->context_class)::G()->options['namespace'] ?? 'unkown');
        $file = $path_lock . $namespace . '.installed';
        return @file_put_contents($file,DATE(DATE_ATOM));
    }
    protected function initSqlDumper()
    {
        //$option  = $this->options['install_table_prefix'] ? true : false;
        //$option  = $ install_table_prefix? $install_table_prefix;
        
         $options = [
            'sql_dump_include_tables' => $this->searchTables(),
            // 'sql_dump_data_tables' => [],        
            'sql_dump_prefix' => '',
            'sql_dump_file' => 'sql',
            'sql_dump_install_replace_prefix' => false,
            'sql_dump_install_new_prefix' => '',
            'sql_dump_install_drop_old_table' => $this->options['install_force'],
        ];
        
        return SqlDumper::G()->init($options, ($this->context_class));
    }
    protected function searchTables()
    {
        $ref = new \ReflectionClass ($this->context_class);
        $file = $ref->getFileName();
        $path = dirname(dirname(''.$file)).'/'.'Model';
        
        $namespace = ($this->context_class)::G()->plugin_options['plugin_namespace'] ?? (($this->context_class)::G()->options['namespace'] ?? 'unkown');
        $namespace = $namespace.'\\'.'Model';
        
        $models = $this->searchModelClasses($path);
        
        $ret=[];
        foreach($models as $k){
            try{
                $class = $namespace.'\\'.'Model\\'.$k;
                $ret[] = $k::G()->table();
            }catch (\Exception $ex){
            }
        }
        //
        $ret = array_values(array_unique(array_filter($ret)));
        return $ret;
    }
    protected function searchModelClasses($path)
    {
        $ret = [];
        $setting_file = !empty($setting_file) ? $path.$setting_file . '.php' : '';
        $flags = \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::FOLLOW_SYMLINKS ;
        $directory = new \RecursiveDirectoryIterator($path, $flags);
        $it = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($it, '/^.+\.php$/i', \RecursiveRegexIterator::MATCH);
        foreach ($regex as $k => $v) {
            $k = substr($v->getSubPathName(), 0, -4);
            $ret[] = $k;
        }
        return $ret;
    }
}