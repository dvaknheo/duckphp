<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\FastInstaller;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\App;


class Supporter extends ComponentBase
{
    public $options = [
        'database_driver_supporter_map'=>[
          'mysql' => SupporterByMySql::class,
          'sqlite' => SupporterBySqlite::class,
          ],
          // change.
    ];
    
    public static function Current()
    {
        return static::_()->getSupporter();
    }
    public function getSupporter()
    {
        $driver = App::Current()->options['database_driver'];
        $new_class = $this->options['database_driver_supporter_map'][$driver] ?? static::class;
        return $new_class::_();
    }
    
    ///////////////
    public function getInstallDesc()
    {
        throw new \Exception('No Impelement');
    }

    public function readDsnSetting($options)
    {
        $driver = App::Current()->options['database_driver'];
        if (!isset($options['dsn'])) {
            return $options;
        }
        $dsn = $options['dsn'];
        $data = substr($dsn, strlen($driver.':'));
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
    public function writeDsnSetting($options)
    {
        throw new \Exception('No Impelement');
    }
    
    public function getAllTable()
    {
        throw new \Exception('No Impelement');
    }
    public function getSchemeByTable($table)
    {
        throw new \Exception('No Impelement');
    }
    
}