<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Ext\InstallerException;
use DuckPhp\Ext\SqlDumper;
use DuckPhp\ThrowOn\ThrowOnableTrait;

class Installer extends ComponentBase
{
    use ThrowOnableTrait;
    
    public $options = [
        'path_install_lock' => 'config',
        'install_force' => false,
        'install_table_prefix' => '',
        'install_sql_dump_options' => [],
        'install_exception_class' => InstallerException::class,
    ];
    //
    public function isInstalled()
    {
        $path_lock = $this->getComponentPath($this->options['path_install_lock'], ($this->context_class)::G()->options['path']);
        $namespace = ($this->context_class)::G()->plugin_options['plugin_namespace'] ?? (($this->context_class)::G()->options['namespace'] ?? '');
        if (!$namespace) {
            return false;
        }
        $namespace = str_replace('\\', '__', $namespace);
        $file = $path_lock . $namespace. '.installed';
        return is_file($file);
    }
    ////////////////
    public function checkInstall()
    {
        $this->exception_class = $this->options['install_exception_class'];
        $has_database = (($this->context_class)::Setting('database') || ($this->context_class)::Setting('database_list')) ? true : false;
        static::ThrowOn(!$has_database, '你需要外部配置，如数据库等', InstallerException::NEED_DATABASE);
        $flag = $this->isInstalled();
        static::ThrowOn(!$flag, "你需要安装", InstallerException::NEED_INSTALL);
    }
    //////////////////

    public function install($options = [])
    {
        $this->exception_class = $this->options['install_exception_class'];
        
        $info = '';
        static::ThrowOn(!$this->options['install_force'] && $this->isInstalled(), '你已经安装 !', InstallerException::INSTALLLED);
        
        //  ext 里的还要安装
        
        try {
            $this->initSqlDumper();
            $info = SqlDumper::G()->install();
        } catch (\Exception $ex) {
            static::ThrowOn(true, "写入数据库失败:" . $ex->getMessage(), InstallerException::INSTALLL_DATABASE_FAILED);
        }
        if ($info) {
            return $info;
        }
        $flag = $this->writeLock();
        static::ThrowOn(!$flag, '写入锁文件失败', InstallerException::INSTALLL_LOCK_FAILED);
        
        return $info;
    }
    public function dumpSql()
    {
        return $this->initSqlDumper()->run();
    }
    /////////////////////////////
    protected function writeLock()
    {
        $path_lock = $this->getComponentPath($this->options['path_install_lock'], ($this->context_class)::G()->options['path']);
        $namespace = ($this->context_class)::G()->plugin_options['plugin_namespace'] ?? (($this->context_class)::G()->options['namespace'] ?? 'unkown');
        $namespace = str_replace('\\', '__', $namespace);
        $file = $path_lock . $namespace . '.installed';

        return file_put_contents($file, DATE(DATE_ATOM));
    }
    protected function initSqlDumper()
    {
        $path = ($this->context_class)::G()->plugin_options['plugin_path'] ?? (($this->context_class)::G()->options['path'] ?? '');
        
        $options = [
            'path' => $path,
            //'path_sql_dump' => 'config',
            //'sql_dump_exclude_tables' => [],
            //'sql_dump_data_tables' => [],
            
            //'sql_dump_prefix' => '',
            //'sql_dump_file' => 'sql',
            'sql_dump_install_replace_prefix' => $this->options['install_table_prefix']?true:false,
            'sql_dump_install_new_prefix' => $this->options['install_table_prefix'],
            'sql_dump_install_drop_old_table' => $this->options['install_force'],
        ];
        $options = array_merge($this->options['install_sql_dump_options'], $options);
        $class = get_class(SqlDumper::G());
        SqlDumper::G(new $class);
        return SqlDumper::G()->init($options, ($this->context_class)::G());
    }
}
