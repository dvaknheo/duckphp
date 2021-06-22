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
        'sql_dump_inlucde_tables' => '*',
        'sql_dump_exclude_tables' => [],
        'sql_dump_data_tables' => [],
        
        'sql_dump_prefix' => '',
        'sql_dump_file' => 'sql',
        'sql_dump_install_replace_prefix' => false,
        'sql_dump_install_new_prefix' => '',
        'sql_dump_install_drop_old_table' => false,
        
    ];
    protected $context_class = null;
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
    }
    public function run()
    {
        $data = $this->getData();
        $this->save($data);
        return true;
    }
    public function install($force = false)
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
        $include_tables = $this->options['sql_dump_inlucde_tables'];
        $ignore_tables = $this->options['sql_dump_exclude_tables'];
        $prefix = $this->options['sql_dump_prefix'];
        $ret = [];
        $data = ($this->context_class)::Db()->fetchAll('show tables');
        foreach ($data as $v) {
            $t = array_values($v);
            $table = $t[0];
            if ((!empty($prefix)) && (substr($table, 0, strlen($prefix)) !== $prefix)) {
                continue;
            }
            if ($include_tables != '*' && !in_array($table, $include_tables)) {
                continue;
            }
            if (in_array($table, $ignore_tables)) {
                continue;
            }
            $ret[$table] = $this->getSchemeByTable($table);
        }
        return $ret;
    }
    
    protected function getSchemeByTable($table)
    {
        $record = ($this->context_class)::Db()->fetch('show create table '.$table);
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
        $path = parent::getComponenetPathByKey('path_sql_dump');
        
        $file = $path.$this->options['sql_dump_file'].'.php';
        $ret = (function () use ($file) {
            return @include $file;
        })();
        return $ret;
    }
    protected function save($data)
    {
        $path = parent::getComponenetPathByKey('path_sql_dump');
        $header = '<'.'?php return ';
        $file = $path.$this->options['sql_dump_file'].'.php';
        $str = $header . var_export($data, true) . ";\n";
        file_put_contents($file, $str);
    }
}
