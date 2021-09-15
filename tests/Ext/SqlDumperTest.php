<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\SqlDumper;
use tests_Data_SqlDumper\System\SqlDumperApp;
use tests_Data_SqlDumper\Model\EmptyModel;
class SqlDumperTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SqlDumper::class);
        
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(SqlDumper::class);
        $setting = include $path_app . 'config/setting.php';
        $options=[
            'setting'=>$setting,
            'path_sql_dump' =>$path_app,
        ];
        DuckPhp::G(new DuckPhp())->init($options);
        SqlDumper::G()->init(DuckPhp::G()->options,DuckPhp::G());

        $sql = "DROP TABLE IF EXISTS `empty`;";
        DuckPhp::Db()->execute($sql);

        $sql="CREATE TABLE `empty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='keep me empty'";
        DuckPhp::Db()->execute($sql);

$sql = "INSERT INTO `empty` (`id`, `data`) VALUES (NULL, '11');";
DuckPhp::Db()->execute($sql);


        SqlDumper::G()->run();
        SqlDumper::G()->install();
        
        SqlDumper::G()->options['sql_dump_data_tables']=['empty'];
        SqlDumper::G()->run();
        SqlDumper::G()->install();
        
        $this->more();
        ////[[[[
        include_once $path_app . 'System/SqlDumperApp.php';
        include_once $path_app . 'Model/EmptyModel.php';
        include_once $path_app . 'Model/NoTableModel.php';
        include_once $path_app . 'Model/ErrorModel.php';
        
        SqlDumperApp::G(new SqlDumperApp())->init($options);
        SqlDumper::G(new SqlDumper())->init(SqlDumperApp::G()->options,SqlDumperApp::G());
        SqlDumper::G()->options['sql_dump_include_tables_all'] = false;
        SqlDumper::G()->options['sql_dump_include_tables_by_model'] = true;
        SqlDumper::G()->options['sql_dump_include_tables'] = ['notable'];
        
        
        SqlDumper::G()->run();
        

$sql= 'DROP TABLE IF EXISTS `empty`';
DuckPhp::Db()->execute($sql);
        ////]]]]
        \LibCoverage\LibCoverage::End();
    }
    protected function more()
    {
        SqlDumper::G()->options['sql_dump_include_tables_all'] = false;
    SqlDumper::G()->options['sql_dump_include_tables_by_model'] = false;
    
        SqlDumper::G(new SqlDumper())->init(DuckPhp::G()->options,DuckPhp::G());
        SqlDumper::G()->options['sql_dump_prefix'] = 'NoExists';
        SqlDumper::G()->run();
        SqlDumper::G()->options['sql_dump_prefix'] = '';
        SqlDumper::G()->options['sql_dump_include_tables'] = ['NoExists'];
        SqlDumper::G()->run();
        SqlDumper::G()->options['sql_dump_prefix'] = '';
        SqlDumper::G()->options['sql_dump_include_tables'] = ['NoExists'];
        SqlDumper::G()->options['sql_dump_exclude_tables'] = ['test'];
        
        SqlDumper::G()->run();
        
        SqlDumper::G()->options['sql_dump_prefix'] = '';
        SqlDumper::G()->options['sql_dump_include_tables'] = ['test'];
        SqlDumper::G()->options['sql_dump_exclude_tables'] = ['test'];
        SqlDumper::G()->run();
        
        SqlDumper::G()->options['sql_dump_prefix'] = '';
        SqlDumper::G()->options['sql_dump_include_tables'] = ['empty'];
        SqlDumper::G()->options['sql_dump_data_tables'] = ['empty'];
        
        SqlDumper::G()->options['sql_dump_include_tables_all'] = false;
        SqlDumper::G()->options['sql_dump_include_tables_by_model'] = false;
        SqlDumper::G()->run();
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
        SqlDumper::G(new SqlDumper())->init($options,DuckPhp::G());
        SqlDumper::G()->install();

        SqlDumper::G(new SqlDumper());
                echo ">>>>>>>>>>>>>>>>>>>>>>>>>\n";

    }
}