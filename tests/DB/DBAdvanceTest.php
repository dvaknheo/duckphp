<?php
namespace tests\DNMVCS\DB;

use DNMVCS\DB\DBAdvance;

class DBAdvanceTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DBAdvance::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(DBAdvance::class);
        $this->assertTrue(true);
        /*
        DBAdvance::G()->quoteIn($array);
        DBAdvance::G()->quoteSetArray($array);
        DBAdvance::G()->qouteInsertArray($array);
        DBAdvance::G()->findData($table_name, $id, $key='id');
        DBAdvance::G()->insertData($table_name, $data, $return_last_id=true);
        DBAdvance::G()->deleteData($table_name, $id, $key='id', $key_delete='is_deleted');
        DBAdvance::G()->updateData($table_name, $id, $data, $key='id');
        //*/
    }
}
