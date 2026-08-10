<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\FastInstaller\Supporter;

class SqlDumper extends ComponentBase
{
    public $options = [
        'path' => '',
        'path_sql_dump' => 'config',

        'sql_dump_include_tables' => [],
        'sql_dump_exclude_tables' => [],
        'sql_dump_data_tables' => [],

        'sql_dump_include_tables_all' => false,
        'sql_dump_include_tables_by_model' => true,

        'sql_dump_debug_show_sql' => false,

    ];
    //protected $spliter = "\n#### DATA BEGIN ####\n";
    protected $spliter = "\n";
    public function dump()
    {
        $driver = DbManager::_()->getDatabaseDriver();
        if (!$driver) {
            return false;
        }
        $scheme = $this->getSchemes();
        $clean = $this->getCleanTableSql();
        $data = $this->getInsertTableSql();

        $this->writeDumpFile($driver.'.sql', $scheme);
        $this->writeDumpFile($driver.'.clean.sql', $clean);
        $this->writeDumpFile($driver.'.data.sql', $data);

        return true;
    }
    protected function writeDumpFile(string $file, string $string): void
    {
        $full_file = $this->extendFullFile($this->options['path'], $this->options['path_sql_dump'], $file);
        file_put_contents($full_file, $string);
    }
    public function install(bool $force = false): void
    {
        $driver = DbManager::_()->getDatabaseDriver();
        $prefix = (string) (App::_()->options['table_prefix'] ?? '');
        $path = $this->options['path'];
        $sub = $this->options['path_sql_dump'];

        if ($force) {
            $file = $this->extendFullFile($path, $sub, $driver.'.clean.sql');
            if (is_file($file)) {
                $this->executeSqlFile($file, $prefix);
            }
        }
        $this->executeSqlFile($this->extendFullFile($path, $sub, $driver.'.sql'), $prefix);
        $file = $this->extendFullFile($path, $sub, $driver.'.data.sql');
        if (is_file($file)) {
            $this->executeSqlFile($file, $prefix);
        }
    }
    protected function executeSqlFile(string $file, string $prefix): void
    {
        $sql = (string) @file_get_contents($file);
        $sql = str_replace('{prefix}', $prefix, $sql);
        $sqls = preg_split('/;\s*(\n|$)/', $sql);
        foreach ($sqls as $sql) {
            if (empty($sql)) {
                continue;
            }
            if ($this->options['sql_dump_debug_show_sql']) {
                echo $sql;
                echo ";\n";
            }
            DbManager::Db()->execute($sql);
        }
    }

    protected function getTables(): array
    {
        $prefix = (string) (App::_()->options['table_prefix'] ?? '');
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
        sort($tables);
        return $tables;
    }
    protected function getSchemes(): string
    {
        $ret = '';
        foreach ($this->getTables() as $table) {
            $sql = Supporter::Current()->getSchemeByTable($table);
            $sql = $this->replacePrefixToPlaceholder($sql);
            $ret .= $sql . ";\n";
        }
        return $ret;
    }
    protected function getCleanTableSql(): string
    {
        $ret = '';
        $prefix = (string) (App::_()->options['table_prefix'] ?? '');
        foreach ($this->getTables() as $table) {
            $name = $table;
            if (($prefix !== '') && (substr($name, 0, strlen($prefix)) === $prefix)) {
                $name = '{prefix}'.substr($name, strlen($prefix));
            }
            $ret .= 'DROP TABLE IF EXISTS '.$name.";\n";
        }
        return $ret;
    }
    protected function replacePrefixToPlaceholder(string $sql): string
    {
        $prefix = (string) (App::_()->options['table_prefix'] ?? '');
        if ($prefix === '') {
            return $sql;
        }
        // handle both quoted (`new_table) and unquoted (new_table) table names
        return str_replace([' `'.$prefix, ' '.$prefix], [' `{prefix}', ' {prefix}'], $sql);
    }
    protected function getInsertTableSql(): string
    {
        $ret = '';
        $tables = $this->options['sql_dump_data_tables'];

        foreach ($tables as $table) {
            $str = $this->getDataSql($table);
            $ret .= $str;
        }
        return $ret;
    }
    protected function getDataSql(string $table): string
    {
        $ret = '';
        $sql = "SELECT * FROM ".DbManager::DbForRead()->qouteScheme($table);
        $data = DbManager::DbForRead()->fetchAll($sql);
        //if (empty($data)) {
        //    return '';
        //}
        foreach ($data as $line) {
            $sql = "INSERT INTO ".DbManager::DbForRead()->qouteScheme($table)." ".DbManager::DbForRead()->qouteInsertArray($line) .";\n";
            $sql = $this->replacePrefixToPlaceholder($sql);
            $ret .= $sql;
        }
        return $ret;
    }

    /////////////////////
    protected function getModelPath(): string
    {
        $namespace = App::_()->options['namespace'];
        $class = $namespace. '\\Model\\Base';
        $ref = new \ReflectionClass($class); /** @phpstan-ignore-line */
        $path = dirname((string)$ref->getFileName());

        return $path;
    }
    protected function searchTables(): array
    {
        $path = $this->getModelPath();
        $namespace = App::_()->options['namespace'];
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
    protected function searchModelClasses(string $path): array
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
