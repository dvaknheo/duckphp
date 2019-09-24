<?php
namespace DNMVCS\DB;

class DB implements DBInterface
{
    use DBAdvance;
    
    public $pdo;
    public $config;
    protected $rowCount;
    protected $driver_options=[
            \PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC
        ];
    public function init($options=[], $context=null)
    {
        $this->config=$options;
        $this->check_connect();
    }
    public static function CreateDBInstance($db_config)
    {
        $class=static::class;
        $db=new $class();
        $db->init($db_config);
        return $db;
    }
    public static function CloseDBInstance($db, $tag=null)
    {
        $db->close();
    }
    protected function check_connect()
    {
        if ($this->pdo) {
            return;
        }
        $config=$this->config;
        $driver_options=$config['driver_options']??[];
        $driver_options=array_replace_recursive($this->driver_options, $driver_options);
        $this->pdo=new \PDO($config['dsn'], $config['username'], $config['password'], $driver_options);
    }
    public function close()
    {
        $this->rowCount=0;
        $this->pdo=null;
    }
    public function getPDO()
    {
        return $this->pdo;
    }
    public function quote($string)
    {
        if (is_array($string)) {
            array_walk(
                $string,
                function (&$v, $k) {
                    $v=is_string($v)?$this->quote($v):(string)$v;
                }
            );
        }
        if (!is_string($string)) {
            return $string;
        }
        $this->check_connect();
        return $this->pdo->quote($string);
    }


    public function fetchAll($sql, ...$args)
    {
        if (count($args)===1 &&is_array($args[0])) {
            $args=$args[0];
        }
        
        $sth = $this->pdo->prepare($sql);
        $sth->execute($args);
        
        $ret=$sth->fetchAll();
        return $ret;
    }
    public function fetch($sql, ...$args)
    {
        if (count($args)===1 &&is_array($args[0])) {
            $args=$args[0];
        }
        
        $sth = $this->pdo->prepare($sql);
        $sth->execute($args);
        $ret=$sth->fetch();
        return $ret;
    }
    public function fetchColumn($sql, ...$args)
    {
        if (count($args)===1 &&is_array($args[0])) {
            $args=$args[0];
        }
        
        $sth = $this->pdo->prepare($sql);
        $sth->execute($args);
        $ret=$sth->fetchColumn();
        return $ret;
    }
    public function execQuick($sql, ...$args)
    {
        if (count($args)===1 &&is_array($args[0])) {
            $args=$args[0];
        }
        
        $sth = $this->pdo->prepare($sql);
        $ret=$sth->execute($args);
        
        $this->rowCount=$sth->rowCount();
        return $ret;
    }
    public function rowCount()
    {
        return $this->rowCount;
    }
}
