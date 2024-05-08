<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\FastInstaller;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class SqlDumper extends ComponentBase
{
    public $options = [
        'path' => '',
        'path_sql_dump' => 'config',
        'sql_dump_file' => 'install.sql',
        
        'sql_dump_include_tables' => [],
        'sql_dump_exclude_tables' => [],
        'sql_dump_data_tables' => [],
        
        'sql_dump_include_tables_all' => false,
        'sql_dump_include_tables_by_model' => true,
        
        'sql_dump_install_replace_prefix' => false,
        'sql_dump_prefix' => '',
        
    ];
    //protected $spliter = "\n#### DATA BEGIN ####\n";
    protected $spliter = "\n";
    public function dump()
    {
        if (!(App::Current()->options['database_driver'])) {
            return;
        }
        $scheme = $this->getSchemes();
        $data = $this->getInsertTableSql();
        
        $file = App::Current()->options['database_driver'].'.sql';
        $full_file = $this->extendFullFile($this->options['path'], $this->options['path_sql_dump'], $file);
        $string = $scheme.$this->spliter.$data;
        file_put_contents($full_file, $string);
        
        return true;
    }
    public function install($force = false)
    {
        if (!(App::Current()->options['database_driver'])) {
            return;
        }
        $file = App::Current()->options['database_driver'].'.sql';
        $full_file = $this->extendFullFile($this->options['path'], $this->options['path_sql_dump'], $file);
        $sql = ''.file_get_contents($full_file);
        
        if ($force) {
            $sql = preg_replace('/CREATE TABLE `([^`]+)`/', 'DROP TABLE IF EXISTS `$1`'.";\n".'CREATE TABLE `$1`', $sql);
        }
        
        if ($this->options['sql_dump_install_replace_prefix']) {
            $prefix = App::Current()->options['table_prefix'];
            $sql = str_replace(' `'.$this->options['sql_dump_prefix'], ' `'.$prefix, ''.$sql);
        }
        $sqls = explode(";\n",$sql);
        foreach($sqls as $sql){
            $flag = DbManager::Db()->execute($sql);
        }
    }
    
    protected function getSchemes()
    {
        $prefix = App::Current()->options['table_prefix'];
        $ret = '';
        $tables = [];
        if ($this->options['sql_dump_include_tables_all']) {
            $tables = Supporter::Current()->getAllTable();
        } else {
            if ($this->options['sql_dump_include_tables_by_model']) {
                $tables = $this->searchTables();
            }
            $included_tables = $this->options['sql_dump_include_tables'];
            $included_tables = str_replace('@', $prefix, $included_tables);
            $tables = array_values(array_unique(array_merge($tables, $included_tables)));
        }
        $tables = array_diff($tables, $this->options['sql_dump_exclude_tables']);
        $tables = array_filter($tables, function ($table) use ($prefix) {
            if ((!empty($prefix)) && (substr($table, 0, strlen($prefix)) !== $prefix)) {
                return false;
            }
            return true;
        });
        foreach ($tables as $table) {
            //try{
            $sql = Supporter::Current()->getSchemeByTable($table);
            //}catch(\Exception $ex){
            //    continue;
            //}
            $ret .= $sql . ";\n";
        }
        return $ret;
    }
    protected function getInsertTableSql()
    {
        $ret = '';
        $tables = $this->options['sql_dump_data_tables'];
        
        foreach ($tables as $table) {
            $str = $this->getDataSql($table);
            $ret .= $str;
        }
        return $ret;
    }
    protected function getDataSql($table)
    {
        $ret = '';
        $sql = "SELECT * FROM `$table`";
        $data = DbManager::DbForRead()->fetchAll($sql);
        //if (empty($data)) {
        //    return '';
        //}
        foreach ($data as $line) {
            $ret .= "INSERT INTO `$table` ".DbManager::DbForRead()->qouteInsertArray($line) .";\n";
        }
        return $ret;
    }

    /////////////////////
    protected function getModelPath()
    {
        $namespace = App::Current()->options['namespace'];
        $class = $namespace. '\\Model\\Base';
        $ref = new \ReflectionClass($class); /** @phpstan-ignore-line */
        $path = dirname((string)$ref->getFileName());

        return $path;
    }
    protected function searchTables()
    {
        $path = $this->getModelPath();
        $namespace = App::Current()->options['namespace'];
        $models = $this->searchModelClasses($path);
        
        $ret = [];
        foreach ($models as $k) {
            try {
                $class = str_replace("/", "\\", $namespace.'/Model'.substr($k, strlen($path)));
                $ret[] = $class::_()->table();
            } catch (\Throwable $ex) {
            }
        }
        $ret = array_values(array_unique(array_filter($ret)));
        return $ret;
    }
    protected function searchModelClasses($path)
    {
        $ret = [];
        $flags = \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::FOLLOW_SYMLINKS ;
        $directory = new \RecursiveDirectoryIterator($path, $flags);
        $it = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($it, '/^.+\.php$/i', \RecursiveRegexIterator::MATCH);
        foreach ($regex as $k => $v) {
            $v = substr($v, 0, -4);  // = getSubPathName()
            $ret[] = $v;
        }
        return $ret;
    }
}
