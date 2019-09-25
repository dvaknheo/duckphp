<?php 
namespace tests\DNMVCS\DB;
use DNMVCS\DB\DB;

class DBTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DB::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(DB::class);
        $this->assertTrue(true);
        /*
        DB::G()->init($options=[], $context=null);
        DB::G()->CreateDBInstance($db_config);
        DB::G()->CloseDBInstance($db, $tag=null);
        DB::G()->check_connect();
        DB::G()->close();
        DB::G()->getPDO();
        DB::G()->quote($string);
        DB::G()->fetchAll($sql, ...$args);
        DB::G()->fetch($sql, ...$args);
        DB::G()->fetchColumn($sql, ...$args);
        DB::G()->execQuick($sql, ...$args);
        DB::G()->rowCount();
        //*/
    }
}
