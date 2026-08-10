<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhpAllInOne as DuckPhp;
use DuckPhp\Component\DbManager;
use DuckPhp\Ext\SqlDumper;
use tests_Data_SqlDumper\Model\EmptyModel;
class SqlDumperTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SqlDumper::class);
        
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(SqlDumper::class);
        // clean leftover artifacts from previous runs (failed tests leave them behind)
        \LibCoverage\LibCoverage::G()->cleanTestDb();
        @unlink($path_app.'config/sqlite.sql');
        @unlink($path_app.'config/sqlite.clean.sql');
        @unlink($path_app.'config/sqlite.data.sql');
        include_once $path_app . 'Model/Base.php';
        
        $setting = include $path_app . 'config/setting.php';
        $options=[
            'setting'=>$setting,
            'path' => $path_app,
            'path_sql_dump' => 'config',
            'namespace' =>'tests_Data_SqlDumper',
            'database_driver'=>'sqlite',
            'sql_dump_debug_show_sql'=>true,
        ];

        DuckPhp::_(new DuckPhp())->init($options);
        SqlDumper::_()->init(DuckPhp::_()->options,DuckPhp::_());
        
        //DuckPhp::_()->options['database_driver']='';
        //SqlDumper::_()->dump();
        //SqlDumper::_()->install();
        DuckPhp::_()->options['database_driver']='sqlite';

        $sql = "DROP TABLE IF EXISTS empty";
        DbManager::Db()->execute($sql);

        $sql="CREATE TABLE empty (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  data INTEGER NOT NULL
)";
        DbManager::Db()->execute($sql);

        $sql = "INSERT INTO empty (id, data) VALUES (1, '11');";
        DbManager::Db()->execute($sql);
        SqlDumper::_()->dump();
        SqlDumper::_()->options['sql_dump_include_tables_all']=true;
        SqlDumper::_()->options['sql_dump_include_tables_by_model']=false;
        SqlDumper::_()->options['sql_dump_data_tables']=['empty'];
        SqlDumper::_()->dump();
        // dump now writes three files: sql / clean / data
        $this->assertFileExists($path_app.'config/sqlite.sql');
        $this->assertFileExists($path_app.'config/sqlite.clean.sql');
        $this->assertFileExists($path_app.'config/sqlite.data.sql');
        $this->assertStringContainsString('CREATE TABLE', file_get_contents($path_app.'config/sqlite.sql'));
        $this->assertStringContainsString('DROP TABLE IF EXISTS empty', file_get_contents($path_app.'config/sqlite.clean.sql'));
        $this->assertStringContainsString('INSERT INTO', file_get_contents($path_app.'config/sqlite.data.sql'));
//try{
        SqlDumper::_()->install(true);
//}catch(\Exception $ex){}
//exit;
        // prefix scenario: dump writes {prefix} placeholder, install replaces it
        $sql = "CREATE TABLE new_empty (id INTEGER PRIMARY KEY AUTOINCREMENT, data INTEGER NOT NULL)";
        DbManager::Db()->execute($sql);
        $sql = "INSERT INTO new_empty (id, data) VALUES (1, '11');";
        DbManager::Db()->execute($sql);
        DuckPhp::_()->options['table_prefix']='new_';
        \DuckPhp\Core\App::_()->options['table_prefix']='new_';
        SqlDumper::_()->options['sql_dump_include_tables_all']=true;
        SqlDumper::_()->options['sql_dump_include_tables_by_model']=false;
        SqlDumper::_()->options['sql_dump_data_tables']=['new_empty'];

        SqlDumper::_()->dump();
        $this->assertStringContainsString('{prefix}empty', file_get_contents($path_app.'config/sqlite.sql'));
        $this->assertStringContainsString('DROP TABLE IF EXISTS {prefix}empty', file_get_contents($path_app.'config/sqlite.clean.sql'));
//try{
        SqlDumper::_()->install(true);
//}catch(\Exception $ex){}
        $rows = DbManager::Db()->fetchAll('SELECT * FROM new_empty');
        $this->assertCount(1, $rows);
        SqlDumper::_()->dump();// 这段需要测试通过
        


    $sql= 'DROP TABLE IF EXISTS empty';
    DbManager::Db()->execute($sql);
        $sql= 'DROP TABLE IF EXISTS new_pty';
    DbManager::Db()->execute($sql);
        
        
        
        
        $sql_file = $path_app.'config/sqlite.sql';
        @unlink($sql_file);
        @unlink($path_app.'config/sqlite.clean.sql');
        @unlink($path_app.'config/sqlite.data.sql');
        ////[[[[
        $options = [
        'database_driver' => '',
        'database' => null,
        'database_list' => [],
        'database_list_reload_by_setting' => false,
        ];
        DbManager::_(new DbManager())->init($options);
        $t = DbManager::_()->getDatabaseDriver();

        SqlDumper::_()->dump();
        //SqlDumper::_()->install();
        
        ////]]]]
        \LibCoverage\LibCoverage::G()->cleanTestDb();
        \LibCoverage\LibCoverage::End();
    }
}
class SqlDumperApp extends DuckPhp
{
    public $options =[
        'namespace' => 'tests_Data_SqlDumper',
    ];
}