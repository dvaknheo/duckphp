<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\FastInstaller;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\ComponentBase;


class Supporter extends ComponentBase
{
    protected $driver;
    public function readSetting($options)
    {
        //'host' => '127.0.0.1','port' => '3306',
        if (!isset($options['dsn'])) {
            return $options;
        }
        $dsn = $options['dsn'];
        $data = substr($dsn, strlen($this->driver.':'));
        $a = explode(';', trim($data, ';'));
        
        $t = array_map(function ($v) {
            return explode("=", $v);
        }, $a);
        $new = array_column($t, 1, 0);
        $new = array_map('trim', $new);
        $new = array_map('stripslashes', $new);
        $options = array_merge($options, $new);
        return $options;
    }
    public function writeSetting($options)
    {
        $options = array_map('trim', $options);
        $options = array_map('addslashes', $options);
        
        $dsn = "mysql:host={$options['host']};port={$options['port']};dbname={$options['dbname']};charset=utf8mb4;";
        
        $options['dsn'] = $dsn;
        unset($options['host']);
        unset($options['port']);
        unset($options['dbname']);
        
        return $options;
    }
    
    public function fromDriver($driver)
    {
        $new_class= static::class;
        $new_class .= 'By'.ucfirst($driver);
        return new $new_class;
    }
    public function getSchemeByTable($table)
    {
    }
    public function getDataSql($table)
    {
    }
    protected function getDsnArray()
    {
    }
}