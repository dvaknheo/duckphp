<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\FastInstaller;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\App;

class SqlDumper extends ComponentBase
{
    public $options = [
        'path' => '',
        'path_sql_dump' => 'config',
        'sql_dump_file' => 'install.sql',
        
        'sql_dump_include_tables' => [],
        'sql_dump_exclude_tables' => [],
        'sql_dump_data_tables' => [],
        
        'sql_dump_include_tables_all' => true,
        'sql_dump_include_tables_by_model' => false,
        
        'sql_dump_install_replace_prefix' => false,
        'sql_dump_install_new_prefix' => null,
        
        'sql_dump_install_drop_old_table' => false,
    ];
    protected $spliter="\n#### DATA BEGIN ####\n";
    public function dump()
    {
        $scheme = $this->getSchemes();
        $data = $this->getInsertTableSql();        
        
        $file = App::Current()->options['database_driver'];
        $file .= '.sql';
        $full_file = $this->extendFullFile($this->options['path'], $this->options['path_sql_dump'], $file);
        $string = $scheme.$this->spliter.$data;
        file_put_contents($full_file, $string);
        
        return true;
    }
    public function install()
    {
        $file = $this->options['sql_dump_file']; // $driver.sql;
        $full_file = $this->extendFullFile($this->options['path'], $this->options['path_sql_dump'], $file);
        $sql = file_get_contents($full_file);
        
        if ($this->options['sql_dump_install_replace_prefix']) {
            $prefix = App::Current()->options['table_prefix'];
            $sql = str_replace('`'.$this->options['sql_dump_prefix'], '`'.$prefix, $sql);
        }
        if ($this->options['sql_dump_install_drop_old_table']) {
            //$sql_delete = "DROP TABLE IF EXISTS `$table`;"; //replace before Create table if not exists
            //DROP TABLE IF EXISTS `$table`; CREATE TABLE IF NOT EXISTS "wa_admins" (

        }
        
        DbManager::Db()->execute($sql);
    }
    
    protected function getSchemes()
    {
        $prefix = App::Current()->options['table_prefix'];
        $ret = '';
        $tables = [];
        if ($this->options['sql_dump_include_tables_all']) {
            $tables  = Supporter::Current()->getAllTable();
        } else {
            if ($this->options['sql_dump_include_tables_by_model']) {
                $tables = $this->searchTables();
            }
            $tables = array_values(array_unique(array_merge($tables,$this->options['sql_dump_include_tables'])));            
        }
        $tables = array_diff($tables, $this->options['sql_dump_exclude_tables']);
        $tables = array_filter($tables, function($table)use($prefix) {
            if ((!empty($prefix)) && (substr($table, 0, strlen($prefix)) !== $prefix)) {
                return false;
            }
            return true;
        });
        foreach ($tables as $table) {
            $sql = Supporter::Current()->getSchemeByTable($table);
            if (!$sql) {
                continue;
            }
            $ret.= $sql . "\n";
        }
        return $ret;
    }   
    protected function getInsertTableSql()
    {
        $ret = '';
        $tables = $this->options['sql_dump_data_tables'];
        
        foreach ($tables as $table) {
            $str = $this->getDataSql($table);
            if (empty($str)) {
                continue;
            }
            $ret.= $str;
        }
        return $ret;
    }
    protected function getDataSql($table)
    {
        $ret = '';
        $sql = "SELECT * FROM `$table`";
        $data = DbManager::DbForRead()->fetchAll($sql);
        
        if (empty($data)) {
            return '';
        }
        foreach ($data as $line) {
            $ret .= "INSERT INTO `$table` ".DbManager::DbForRead()->qouteInsertArray($line) .";\n";
        }
        return $ret;
    }

    /////////////////////
    protected function searchTables()
    {
        //TODO be a method
        $namespace = $this->context()->options['namespace'];
        $class = $namespace. '\\Model\\Base';
        $ref = new \ReflectionClass($class); /** @phpstan-ignore-line */
        
        $path = dirname((string)$ref->getFileName());
        
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
