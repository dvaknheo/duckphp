<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\DuckPhp as DuckPhp;
use DuckPhp\Component\DbManager;

use DuckPhp\FastInstaller\SupporterBySqlite;
use DuckPhp\FastInstaller\Supporter;

class SupporterBySqliteTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SupporterBySqlite::class);
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
     
        Supporter::Current()->getInstallDesc();
     
        $t = Supporter::Current()->readDsnSetting($options['database_list'][0]);
        Supporter::Current()->writeDsnSetting($t);

        
        Supporter::Current()->getAllTable();
        Supporter::Current()->getSchemeByTable('table');

        @unlink($file);
        
        
        
        DuckPhp::Current()->options['local_database']= true;
        $t = Supporter::Current()->readDsnSetting([]);
        DuckPhp::Current()->options['local_database']= false;
        $t = Supporter::Current()->readDsnSetting([]);

        
        //@unlink($file2);
        
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
