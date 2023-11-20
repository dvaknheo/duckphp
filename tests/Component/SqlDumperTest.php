<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\DuckPhpAllInOne as DuckPhp;
use DuckPhp\Component\SqlDumper;
use tests_Data_SqlDumper\System\SqlDumperApp;
use tests_Data_SqlDumper\Model\EmptyModel;
class SqlDumperTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SqlDumper::class);
        
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(SqlDumper::class);
        $setting = include $path_app . 'config/setting.php';  //
        $options=[
            'setting'=>$setting,
            'path_sql_dump' =>$path_app,
        ];
        DuckPhp::_(new DuckPhp())->init($options);
        SqlDumper::_()->init(DuckPhp::_()->options,DuckPhp::_());

        $sql = "DROP TABLE IF EXISTS `empty`;";
        DuckPhp::Db()->execute($sql);

        $sql="CREATE TABLE `empty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='keep me empty'";
        DuckPhp::Db()->execute($sql);

$sql = "INSERT INTO `empty` (`id`, `data`) VALUES (1, '11');";
DuckPhp::Db()->execute($sql);


        SqlDumper::_()->run();
        SqlDumper::_()->install();
        
        SqlDumper::_()->options['sql_dump_data_tables']=['empty'];
        SqlDumper::_()->run();
        SqlDumper::_()->install();

$sql = "delete from `empty` where id =1";
DuckPhp::Db()->execute($sql);
        
        SqlDumper::_()->options['sql_dump_data_tables']=['empty'];
        SqlDumper::_()->run();
        SqlDumper::_()->install();
        
        $this->more();
        ////[[[[
        include_once $path_app . 'System/SqlDumperApp.php';
        include_once $path_app . 'Model/EmptyModel.php';
        include_once $path_app . 'Model/NoTableModel.php';
        include_once $path_app . 'Model/ErrorModel.php';
        
        SqlDumperApp::_(new SqlDumperApp())->init($options);
        SqlDumper::_(new SqlDumper())->init(SqlDumperApp::_()->options,SqlDumperApp::_());
        SqlDumper::_()->options['sql_dump_include_tables_all'] = false;
        SqlDumper::_()->options['sql_dump_include_tables_by_model'] = true;
        SqlDumper::_()->options['sql_dump_include_tables'] = ['notable'];
        
        
        SqlDumper::_()->run();
        

$sql= 'DROP TABLE IF EXISTS `empty`';
DuckPhp::Db()->execute($sql);
        ////]]]]
        \LibCoverage\LibCoverage::End();
    }
    protected function more()
    {
        SqlDumper::_()->options['sql_dump_include_tables_all'] = false;
    SqlDumper::_()->options['sql_dump_include_tables_by_model'] = false;
    
        SqlDumper::_(new SqlDumper())->init(DuckPhp::_()->options,DuckPhp::_());
        SqlDumper::_()->options['sql_dump_prefix'] = 'NoExists';
        SqlDumper::_()->run();
        SqlDumper::_()->options['sql_dump_prefix'] = '';
        SqlDumper::_()->options['sql_dump_include_tables'] = ['NoExists'];
        SqlDumper::_()->run();
        SqlDumper::_()->options['sql_dump_prefix'] = '';
        SqlDumper::_()->options['sql_dump_include_tables'] = ['NoExists'];
        SqlDumper::_()->options['sql_dump_exclude_tables'] = ['test'];
        
        SqlDumper::_()->run();
        
        SqlDumper::_()->options['sql_dump_prefix'] = '';
        SqlDumper::_()->options['sql_dump_include_tables'] = ['test'];
        SqlDumper::_()->options['sql_dump_exclude_tables'] = ['test'];
        SqlDumper::_()->run();
        
        SqlDumper::_()->options['sql_dump_prefix'] = '';
        SqlDumper::_()->options['sql_dump_include_tables'] = ['empty'];
        SqlDumper::_()->options['sql_dump_data_tables'] = ['empty'];
        
        SqlDumper::_()->options['sql_dump_include_tables_all'] = false;
        SqlDumper::_()->options['sql_dump_include_tables_by_model'] = false;
        SqlDumper::_()->run();
        ///////////////////////
        echo "<<<<<<<<<<<<<<<<<<<<<<<<\n";
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(SqlDumper::class);
        $options = [
            'path_sql_dump' => $path_app,
            'sql_dump_include_tables_all' => false,
            'sql_dump_include_tables_by_model' => false,
            'sql_dump_include_tables' => ['empty'],
            'sql_dump_prefix' => 'em',
            'sql_dump_install_replace_prefix' => true,
            'sql_dump_install_new_prefix' => 'newprefix',
            'sql_dump_install_drop_old_table' => true,
        
        ];
        SqlDumper::_(new SqlDumper())->init($options,DuckPhp::_());
        SqlDumper::_()->install();

        SqlDumper::_(new SqlDumper());
                echo ">>>>>>>>>>>>>>>>>>>>>>>>>\n";

    }
}