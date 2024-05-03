<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\DuckPhp;
use DuckPhp\Component\DbManager;

use DuckPhp\FastInstaller\Supporter;
use DuckPhp\Db\Db;
use DuckPhp\FastInstaller\SupporterByMysql;
//use tests_Data_SqlDumper\Model\EmptyModel;

class SupporterByMysqlTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SupporterByMysql::class);
        $path_setting = \LibCoverage\LibCoverage::G()->getClassTestPath(Db::class);
        $setting = include $path_setting . 'setting.php';
        $database_list = $setting['database_list'];
        
        $options =[
            'database_driver'=>'mysql',
            'database_list' => $database_list,
        ];
        DuckPhp::_()->init($options);
        $this->makeData();
        Supporter::Current()->getInstallDesc();
        Supporter::Current()->readDsnSetting([]);
        Supporter::Current()->writeDsnSetting([]);
        Supporter::Current()->getAllTable();
        Supporter::Current()->getSchemeByTable('empty');
        
        $this->cleanData();
        
        \LibCoverage\LibCoverage::End();
    }
    protected function makeData()
    {
        $sql = "DROP TABLE IF EXISTS `empty`;";
        DbManager::Db()->execute($sql);

        $sql="CREATE TABLE `empty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='keep me empty'";
        DbManager::Db()->execute($sql);

$sql = "INSERT INTO `empty` (`id`, `data`) VALUES (1, '11');";
DbManager::Db()->execute($sql);

    }
    protected function cleanData()
    {
        $sql = "DROP TABLE IF EXISTS `empty`;";
        DbManager::Db()->execute($sql);

    }
}
