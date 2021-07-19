<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System;

use DuckPhp\Core\App;
use DuckPhp\Core\Configer;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Foundation\SqlDumper;
use DuckPhp\Foundation\ThrowOnableTrait;

class Installer extends ComponentBase // @codeCoverageIgnoreStart
{
    use ThrowOnableTrait;
    
    public $options = [
        'install_lock_file' => '???',
        'force' => false,
    ];
    protected $path_lock;
    
    public function __construct()
    {
        parent::__construct();
        $this->exception_class = \ErrorException::class;
    }
    /*
    protected function checkInstall()
    {
        if (!(App::Setting('database') ||  App::Setting('database_list'))){
            throw new NeedInstallException('Need Database',NeedInstallException::NEED_DATABASE);
        }
        if (!Installer::G()->init([],$this)->isInstalled()){
            throw new NeedInstallException("", NeedInstallException::NEED_INSTALL);
        }
    }
    public function install($parameters)
    {
        $options = [
            'force' => $parameters['force']?? false,
            'path' => $this->getPath(),
            
            'sql_dump_prefix' => '',
            'sql_dump_inlucde_tables' => [ 'Users'],        
            'sql_dump_install_replace_prefix' => true,
            'sql_dump_install_new_prefix' => $this->options['simple_auth_table_prefix'],
            'sql_dump_install_drop_old_table' => $parameters['force']?? false,
        ];
        Installer::G()->init($options,$this);
        
        echo Installer::G()->run();
    }
    */
    
    //@override
    public function init(array $options, ?object $context = NULL)
    {
        parent::init($options, $context);
        
        $this->path_lock = $this->getComponenetPath(Configer::G()->options['path_config'],Configer::G()->options['path']);
        $this->options['path_sql_dump'] = ($this->context_class)::G()->getPath().'config/'; // 这里再灵活一点？// 判断和 app ，是否是插件，如果是插件，我们用另外的来搞
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
           static::ThrowOn(!$flag,'你已经安装 SimpleAuth',-1);     
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
    public function export()
    {
        return SqlDumper::G()->run();
    }// @codeCoverageIgnoreEnd
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