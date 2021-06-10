<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\SqlDumper;

class SqlDumperTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SqlDumper::class);
        
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(Db::class);

        $setting = include $path_app . 'setting.php';
        
        $options=[
            'setting'=>$setting,
            'path_sql_dumper' =>$path_app;
        ];
        DuckPhp(new DuckPhp())->init([]);
        SqlDumper::G($options,DuckPhp::G());
        
        SqlDumper::G()->install();
        
        
        \LibCoverage\LibCoverage::End();
    }
}