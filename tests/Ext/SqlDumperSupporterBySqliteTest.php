<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp as DuckPhp;
use DuckPhp\Component\DbManager;

use DuckPhp\Ext\SqlDumperSupporterBySqlite;
use DuckPhp\Ext\SqlDumperSupporter;

class SqlDumperSupporterBySqliteTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SqlDumperSupporterBySqlite::class);
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        
        $file = $path_app.'data.sqlite';
        
        //@unlink($file);
        $options =[
            'path_runtime' => $path_app,
            'database_driver'=>'sqlite',
            'database_list' =>[
                ['dsn'=>'sqlite:'.$file],
            ],
            
        ];
        DuckPhp::_()->init($options);
        $this->makeData();
     
        SqlDumperSupporter::Current()->getAllTable();
        SqlDumperSupporter::Current()->getSchemeByTable('table');

        @unlink($file);
        
        \LibCoverage\LibCoverage::End();
    }
    protected function makeData()
    {
        $sql = <<<EOT
CREATE TABLE "table" (
	"id"	INTEGER,
	"content"	TEXT,
	PRIMARY KEY("id" AUTOINCREMENT)
);
EOT;
        DbManager::Db()->execute($sql);
    }
}
