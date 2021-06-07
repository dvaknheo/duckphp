<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\SqlDumper;

class SqlDumperTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SqlDumper::class);
        
        SqlDumper::G()->run();
        SqlDumper::G()->install();
        
        \MyCodeCoverage::G()->end();
    }
}