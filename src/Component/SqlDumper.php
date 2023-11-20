<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class SqlDumper extends ComponentBase
{
    public $options = [
        'path' => '',
        'path_sql_dump' => 'config',
        'sql_dump_include_tables' => [],
        'sql_dump_exclude_tables' => [],
        'sql_dump_data_tables' => [],
        
        'sql_dump_include_tables_all' => true,
        'sql_dump_include_tables_by_model' => false,

        'sql_dump_prefix' => '',
        'sql_dump_file' => 'sql',
        'sql_dump_install_replace_prefix' => false,
        'sql_dump_install_new_prefix' => '',
        'sql_dump_install_drop_old_table' => false,
        
    ];
    public function run()
    {
        $data = $this->getData();
        $this->save($data);
        return true;
    }
    public function install()
    {
        $ret = '';
        $data = $this->load();
        foreach ($data['scheme'] as $table => $sql) {
            try {
                $this->installScheme($sql, $table);
            } catch (\PDOException $ex) {
                $ret .= $ex->getMessage() . "\n";
            }
        }
        $data['data'] = $data['data'] ?: [];
        foreach ($data['data'] as $table => $sql) {
            try {
                $this->installData($sql, $table);
            } catch (\PDOException $ex) {
                $ret .= $ex->getMessage() . "\n";
            }
        }
        return $ret;
    }
    protected function installScheme($sql, $table)
    {
        if ($this->options['sql_dump_install_replace_prefix']) {
            $table = $this->options['sql_dump_install_new_prefix'] .substr($table, strlen($this->options['sql_dump_prefix']));
        }
        if ($this->options['sql_dump_install_drop_old_table']) {
            $sql_delete = "DROP TABLE IF EXISTS `$table`";
            DbManager::DbForRead()->execute($sql_delete);
        }
        $sql = preg_replace('/`[^`]+`/', "`$table`", $sql, 1);
        DbManager::DbForRead()->execute($sql);
    }
    protected function installData($sql, $table)
    {
        //todo replace
        DbManager::Db()->execute($sql);
    }

    protected function getData()
    {
        $ret = [];
        $scheme = [];
        
        $ret['scheme'] = $this->getSchemes();
        $ret['data'] = $this->getInsertTableSql();
        
        return $ret;
    }
    protected function getSchemes()
    {
        $prefix = $this->options['sql_dump_prefix'];
        $ret = [];
        $tables = [];
        if ($this->options['sql_dump_include_tables_all']) {
            $data = DbManager::DbForRead()->fetchAll('show tables');
            foreach ($data as $v) {
                $tables[] = array_values($v)[0];
            }
        } else {
            if ($this->options['sql_dump_include_tables_by_model']) {
                $tables = $this->searchTables();
            }
            $tables = array_values(array_unique(array_merge($this->options['sql_dump_include_tables'])));
        }
        $tables = array_diff($tables, $this->options['sql_dump_exclude_tables']);
        foreach ($tables as $table) {
            if ((!empty($prefix)) && (substr($table, 0, strlen($prefix)) !== $prefix)) {
                continue;
            }
            $sql = $this->getSchemeByTable($table);
            if (!$sql) {
                continue;
            }
            $ret[$table] = $sql;
        }
        return $ret;
    }
    
    protected function getSchemeByTable($table)
    {
        try {
            $record = DbManager::DbForRead()->fetch("show create table `$table`");
        } catch (\PDOException $ex) {
            return '';
        }
        $sql = $record['Create Table'] ?? null;
        $sql = preg_replace('/AUTO_INCREMENT=\d+/', 'AUTO_INCREMENT=1', $sql);
        return $sql;
    }
    protected function getInsertTableSql()
    {
        $ret = [];
        $tables = $this->options['sql_dump_data_tables'];
        
        foreach ($tables as $table) {
            $str = $this->getDataSql($table);
            if (empty($str)) {
                continue;
            }
            $ret[$table] = $str;
        }
        return $ret;
    }
    protected function getDataSql($table)
    {
        $ret = '';
        $sql = "SELECT * FROM ".$table;
        $data = DbManager::DbForRead()->fetchAll($sql);
        if (empty($data)) {
            return '';
        }
        foreach ($data as $line) {
            $ret .= "INSERT INTO $table ".DbManager::DbForRead()->qouteInsertArray($line) .";\n";
        }
        return $ret;
    }
    protected function load()
    {
        $ret = [];
        
        $file = $this->options['sql_dump_file'].'.php';
        $full_file = $this->extendFullFile($this->options['path'], $this->options['path_sql_dump'], $file);

        $ret = (function () use ($full_file) {
            return @include $full_file;
        })();
        return $ret;
    }
    protected function save($data)
    {
        $file = $this->options['sql_dump_file'].'.php';
        if (static::IsAbsPath($file)) {
            $full_file = $file;
        } elseif (static::IsAbsPath($this->options['path_sql_dump'])) {
            $full_file = static::SlashDir($this->options['path_sql_dump']) . $file;
        } else {
            $full_file = static::SlashDir($this->options['path']) . static::SlashDir($this->options['path_sql_dump']) . $file;
        }
        $full_file = '' . $full_file;
        
        $string = "<"."?php //". "regenerate by " . __CLASS__ . '->'.__METHOD__ ." at ". DATE(DATE_ATOM) . "\n";
        $string .= "return ".var_export($data, true) . ";\n";
        file_put_contents($full_file, $string);
    }
    /////////////////////
    protected function searchTables()
    {
        $ref = new \ReflectionClass($this->context());
        $file = $ref->getFileName();
        $path = dirname(dirname(''.$file)).'/Model/';
        
        $namespace = $this->context()->options['namespace'];
        $namespace = $namespace.'\\Model\\';
        
        $models = $this->searchModelClasses($path);
        
        $ret = [];
        foreach ($models as $k) {
            try {
                $class = $namespace.$k;
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
        
        $setting_file = !empty($setting_file) ? $path.$setting_file . '.php' : '';
        $flags = \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::FOLLOW_SYMLINKS ;
        $directory = new \RecursiveDirectoryIterator($path, $flags);
        $it = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($it, '/^.+\.php$/i', \RecursiveRegexIterator::MATCH);
        foreach ($regex as $k => $v) {
            $k = substr($path, 0, -4);  // = getSubPathName()
            $ret[] = $k;
        }
        return $ret;
    }
}
