<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\DuckPhpAllInOne as DuckPhp;
use DuckPhp\Component\DbManager;
use DuckPhp\FastInstaller\SqlDumper;
use tests_Data_SqlDumper\Model\EmptyModel;
class SqlDumperTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SqlDumper::class);
        
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(SqlDumper::class);
        include_once $path_app . 'Model/Base.php';
        
        $setting = include $path_app . 'config/setting.php';
        $options=[
            'setting'=>$setting,
            'path' => $path_app,
            'path_sql_dump' => 'config',
            'sql_dump_file' => 'sql.php',
            'namespace' =>'tests_Data_SqlDumper',
            'database_driver'=>'mysql',
        ];

        DuckPhp::_(new DuckPhp())->init($options);
        SqlDumper::_()->init(DuckPhp::_()->options,DuckPhp::_());
        
        DuckPhp::_()->options['database_driver']='';
        SqlDumper::_()->dump();
        SqlDumper::_()->install();
        DuckPhp::_()->options['database_driver']='mysql';

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
        SqlDumper::_()->dump();
        SqlDumper::_()->options['sql_dump_include_tables_all']=true;
        SqlDumper::_()->options['sql_dump_include_tables_by_model']=false;
        SqlDumper::_()->options['sql_dump_data_tables']=['empty'];
        SqlDumper::_()->dump();
        SqlDumper::_()->install(true);


        DuckPhp::_()->options['table_prefix']='new_';
        SqlDumper::_()->options['sql_dump_include_tables_all']=true;
        SqlDumper::_()->options['sql_dump_include_tables_by_model']=false;
        SqlDumper::_()->options['sql_dump_data_tables']=['empty'];

        SqlDumper::_()->options['sql_dump_install_replace_prefix']=true;
        SqlDumper::_()->options['sql_dump_prefix']='em';
        SqlDumper::_()->install(true);
        
        SqlDumper::_()->dump();


    $sql= 'DROP TABLE IF EXISTS `empty`';
    DbManager::Db()->execute($sql);
        $sql= 'DROP TABLE IF EXISTS `new_pty`';
    DbManager::Db()->execute($sql);
        
        $sql_file = $path_app.'config/mysql.sql';
        @unlink($sql_file);

        ////]]]]
        \LibCoverage\LibCoverage::End();
    }
}
class SqlDumperApp extends DuckPhp
{
    public $options =[
        'namespace' => 'tests_Data_SqlDumper',
    ];
}