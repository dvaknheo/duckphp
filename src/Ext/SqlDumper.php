<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

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
                ($this->context_class)::Db()->execute($sql);
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
            ($this->context_class)::Db()->execute($sql_delete);
        }
        $sql = preg_replace('/`[^`]+`/', "`$table`", $sql, 1);
        ($this->context_class)::Db()->execute($sql);
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
        if ($this->options['sql_dump_include_tables_by_model']) {
            $data = ($this->context_class)::Db()->fetchAll('show tables');
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
        
        foreach($tables as $table){
            if ((!empty($prefix)) && (substr($table, 0, strlen($prefix)) !== $prefix)) {
                continue;
            }
            $sql = $this->getSchemeByTable($table);
            if(!$sql){
                continue;
            }
            $ret[$table] = $sql;
        }
        return $ret;
    }
    
    protected function getSchemeByTable($table)
    {
        try {
            $record = ($this->context_class)::Db()->fetch('show create table '.$table);
        } catch(\PDOException $ex) {
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
        $data = ($this->context_class)::Db()->fetchAll($sql);
        if (empty($data)) {
            return '';
        }
        foreach ($data as $line) {
            $ret .= "INSERT INTO $table ".($this->context_class)::Db()->qouteInsertArray($line) .";\n";
        }
        return $ret;
    }
    protected function load()
    {
        $ret = [];
        $path = parent::getComponentPathByKey('path_sql_dump');
        $file = $path.$this->options['sql_dump_file'].'.php';
        
        $ret = (function () use ($file) {
            return @include $file;
        })();
        return $ret;
    }
    protected function save($data)
    {
        $path = parent::getComponentPathByKey('path_sql_dump');
        $header = '<'.'?php return ';
        $file = $path.$this->options['sql_dump_file'].'.php';
        $str = $header . var_export($data, true) . ";\n";
        file_put_contents($file, $str);
    }
    /////////////////////
    protected function searchTables()
    {
        // sqldumper 的内容。
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
