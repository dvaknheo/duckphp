<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\SqlDumper;

class SqlDumperTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SqlDumper::class);
        
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(SqlDumper::class);

        $setting = include $path_app . 'setting.php';
        @unlink(include $path_app . 'sql_struct.php');
        @unlink(include $path_app . 'sql_data.php');
        $database_list = $setting['database_list'];
        
        $database_list = $setting['database_list'][0];
        $options=[
            'setting'=>$setting,
            'path_sql_dump' =>$path_app,
        ];
        DuckPhp::G(new DuckPhp())->init($options);
        SqlDumper::G()->init(DuckPhp::G()->options,DuckPhp::G());
        SqlDumper::G()->run();
        SqlDumper::G()->install();
        
        SqlDumper::G()->options['sql_dump_data_tables']=['Settings'];
        SqlDumper::G()->run();
        SqlDumper::G()->install();
        
        $this->more();
        \LibCoverage\LibCoverage::End();
    }
    protected function more()
    {
        SqlDumper::G(new SqlDumper())->init(DuckPhp::G()->options,DuckPhp::G());
        SqlDumper::G()->options['sql_dump_prefix'] = 'NoExists';
        SqlDumper::G()->run();
        SqlDumper::G()->options['sql_dump_prefix'] = '';
        SqlDumper::G()->options['sql_dump_inlucde_tables'] = ['NoExists'];
        SqlDumper::G()->run();
        SqlDumper::G()->options['sql_dump_prefix'] = '';
        SqlDumper::G()->options['sql_dump_inlucde_tables'] = ['NoExists'];
        SqlDumper::G()->options['sql_dump_exclude_tables'] = ['test'];
        
        SqlDumper::G()->run();
        
        SqlDumper::G()->options['sql_dump_prefix'] = '';
        SqlDumper::G()->options['sql_dump_inlucde_tables'] = ['test'];
        SqlDumper::G()->options['sql_dump_exclude_tables'] = ['test'];
        SqlDumper::G()->run();
        
        SqlDumper::G()->options['sql_dump_prefix'] = '';
        SqlDumper::G()->options['sql_dump_inlucde_tables'] = ['empty'];
        SqlDumper::G()->options['sql_dump_data_tables'] = ['empty'];
        
        SqlDumper::G()->run();
        
    }
}