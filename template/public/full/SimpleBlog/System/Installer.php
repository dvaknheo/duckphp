<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use DuckPhp\Core\Configer;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Foundation\SqlDumper;
use DuckPhp\Foundation\ThrowOnableTrait;

class Installer extends ComponentBase
{
    use ThrowOnableTrait;
    
    public $options = [
        /////
        'install_lock_file' => 'SimpleBlog.lock',
        'force' => false,

    ];
    protected $path_lock;
    public function __construct()
    {
        parent::__construct();
        $this->exception_class = \ErrorException::class;
        //static::ExceptionClass(NeedInstallException::class);
    }
    //@override
    public function init(array $options, ?object $context = NULL)
    {
        parent::init($options, $context);
        
        $this->path_lock = $this->getComponenetPath(Configer::G()->options['path_config'],Configer::G()->options['path']);
        $this->options['path_sql_dump'] = ($this->context_class)::G()->getPath().'config/'; // 这里再灵活一点？
        SqlDumper::G()->init($options, ($this->context_class)::G());
        return $this;
    }
    public function isInstalled()
    {
        $file = $this->path_lock.$this->options['install_lock_file'];
        return is_file($file);
    }
    protected function writeLock()
    {
        $file = $this->path_lock.$this->options['install_lock_file'];
        return @file_put_contents($file,DATE(DATE_ATOM));
    }
    public function run()
    {
        $ret = false;
        if(!$this->options['force'] && $this->isInstalled()){
           static::ThrowOn(!$flag,'你已经安装 SimpleBlog',-1);     
        }
        try{
            $ret = SqlDumper::G()->install($this->options['force']??false);
        }catch(\Exception $ex){
            static::ThrowOn(true, "写入数据库失败:" . $ex->getMessage(),-2);
        }
        if($ret){
            return $ret;
        }
        $flag = $this->writeLock();
        static::ThrowOn(!$flag,'写入锁文件失败',-3);
            
        return $ret;
    }
    public function dumpSql()
    {
        return SqlDumper::G()->run();
    }
    /////////////////////
    protected function getComponenetPath($path, $basepath = ''): string
    {
        // 考虑放到系统里
        if (DIRECTORY_SEPARATOR === '/') {
            if (substr($path, 0, 1) === '/') {
                return rtrim($path, '/').'/';
            } else {
                return $basepath.rtrim($path, '/').'/';
            }
        } else { // @codeCoverageIgnoreStart
            if (substr($path, 1, 1) === ':') {
                return rtrim($path, '\\').'\\';
            } else {
                return $basepath.rtrim($path, '\\').'\\';
            } // @codeCoverageIgnoreEnd
        }
    }
}