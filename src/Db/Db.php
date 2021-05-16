<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Db;

class Db implements DbInterface
{
    use DbAdvanceTrait;
    
    public $pdo;
    public $config;
    protected $tableName;
    protected $resultClass = 'stdClass';
    protected $rowCount;
    protected $beforeQueryHandler = null;
    protected $success = false;
    protected $driver_options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ];
    public function init($options = [], $context = null)
    {
        $this->config = $options;
        $this->check_connect();
    }
    protected function check_connect()
    {
        if ($this->pdo) {
            return;
        }
        $config = $this->config;
        $driver_options = $config['driver_options'] ?? [];
        $driver_options = array_replace_recursive($this->driver_options, $driver_options);
        $this->pdo = new \PDO($config['dsn'], $config['username'], $config['password'], $driver_options);
    }
    public function close()
    {
        $this->rowCount = 0;
        $this->pdo = null;
    }
    public function PDO($pdo = null)
    {
        if ($pdo) {
            $this->pdo = $pdo;
        }
        $this->check_connect();
        return $this->pdo;
    }
    public function setBeforeQueryHandler($handler)
    {
        $this->beforeQueryHandler = $handler;
    }
    public function quote($string)
    {
        if (is_array($string)) {
            array_walk(
                $string,
                function (&$v, $k) {
                    $v = is_string($v)?$this->quote($v):(string)$v;
                }
            );
        }
        if (!is_string($string)) {
            return $string;
        }
        $this->check_connect();
        return $this->pdo->quote($string);
    }
    public function buildQueryString($sql, ...$args)
    {
        if (count($args) === 1 && is_array($args[0])) {
            $keys = $args[0];
            foreach ($keys as $k => $v) {
                $sql = str_replace(':'.$k, $this->quote($v), $sql);
            }
            return $sql;
        }
        if (empty($args)) {
            return $sql;
        }
        $count = 1;
        $sql = str_replace(array_fill(0, count($args), '?'), $args, $sql, $count);
        return $sql;
    }
    /////////////////
    public function table($table_name)
    {
        $this->tableName = $table_name;
        return $this;
    }
    public function doTableNameMacro($sql)
    {
        return empty($this->tableName) ? $sql : str_replace($this->quote('TABLE'), $this->tableName, $sql);
    }
    public function setObjectResultClass($resultClass)
    {
        $this->resultClass = $resultClass;
        return $this;
    }

    protected function exec($sql, ...$args)
    {
        if ($this->beforeQueryHandler) {
            ($this->beforeQueryHandler)($this, $sql, ...$args);
        }
        
        if (count($args) === 1 && is_array($args[0])) {
            $args = $args[0];
        }
        $sql = $this->doTableNameMacro($sql);
        $sth = $this->pdo->prepare($sql);
        $success = $sth->execute($args);
        $this->success = $success;
        return $sth;
    }
    //////////
    public function fetchAll($sql, ...$args)
    {
        return $this->exec($sql, ...$args)->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function fetch($sql, ...$args)
    {
        return $this->exec($sql, ...$args)->fetch(\PDO::FETCH_ASSOC);
    }
    public function fetchColumn($sql, ...$args)
    {
        return $this->exec($sql, ...$args)->fetchColumn();
    }
    public function fetchObject($sql, ...$args)
    {
        return $this->exec($sql, ...$args)->fetchObject($this->resultClass);
    }
    public function fetchObjectAll($sql, ...$args)
    {
        return $this->exec($sql, ...$args)->fetchAll(\PDO::FETCH_CLASS, $this->resultClass);
    }

    //*/
    public function execute($sql, ...$args)
    {
        $sth = $this->exec($sql, ...$args);
        if (!$this->success) {
        }
        $this->rowCount = $this->success ? 0 : $sth->rowCount();
        return $this->success;
    }
    public function rowCount()
    {
        return $this->rowCount;
    }
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
