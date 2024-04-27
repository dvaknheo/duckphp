<?php 
class SupporterByMysql extends Supporter
{
    protected $driver ='mysql'

    
    public function foo()
    {
            $desc = <<<EOT
----
    host: [{host}] 
    port: [{port}]
    dbname: [{dbname}]
    username: [{username}]
    password: [{password}]
EOT;
            return $desc;
    }
    /////////////////////////////////
    public function getSchemeByTable($table)
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
    public function getDataSql($table)
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
    protected function getDsnArray()
    {
        return ['host'=>true,'port'=>true,'dbname'=>true,'username'=>true,'password'=>true,];
    }
protected function configDatabase($ref_database_list = [])
    {
        $ret = [];
        
        $options = [];
        while (true) {
            $j = count($ret);
            echo "Setting MySQL database[$j]:\n";
            $desc = <<<EOT
----
    host: [{host}] 
    port: [{port}]
    dbname: [{dbname}]
    username: [{username}]
    password: [{password}]
EOT;
    
            $options = array_merge($ref_database_list[$j] ?? [], $options);
            
            $options = $this->makeFromDsn($options);
            
            $options = array_merge(['host' => '127.0.0.1','port' => '3306',], $options);
            $options = Console::_()->readLines($options, $desc);
            list($flag, $error_string) = $this->checkDb($options);
            if ($flag) {
                $options['dsn'] = $this->dsnFromSetting($options);
                unset($options['host']);
                unset($options['port']);
                unset($options['dbname']);
                
                $ret[] = $options;
            }
            if (!$flag) {
                echo "Connect database error: $error_string \n";
            }
            if (empty($ret)) {
                continue;
            }
            $sure = Console::_()->readLines(['sure' => 'N'], "Setting More Database(Y/N)[{sure}]?");
            if (strtoupper($sure['sure']) === 'Y') {
                continue;
            }
            break;
        }
        return $ret;
    }
}
