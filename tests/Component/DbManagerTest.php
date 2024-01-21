<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\DbManager;
use DuckPhp\Db\Db;
use DuckPhp\DuckPhpAllInOne as App;

class DbManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DbManager::class);
        
        $path_setting = \LibCoverage\LibCoverage::G()->getClassTestPath(Db::class);
        $setting = include $path_setting . 'setting.php';
        $database_list = $setting['database_list'];
        
        $dn_options=[
            'database_list'=>['zzz'],
        ];
        App::_()->init($dn_options);
        
        $options=[
            'database_list'=>$database_list,
        ];
        DbManager::_()->init($options,App::_());
        DbManager::_(new DbManager());
        DbManager::_()->getDatabaseConfigList();
        $options=[

        'db_before_get_object_handler'=>[null,'beforeGet'],
        
        'database_list'=> $database_list,
            'database_class' => MyDb::class,

        ];
        

        //App::_()->extendComponents(static::class,['beforeGet'],[]);
        DbManager::_()->init($options,App::_());
        $options['database_list']=$database_list;
        DbManager::_()->init($options,null);
        
        DbManager::_()->setBeforeGetDbHandler(function(){var_dump("OK");});

        DbManager::_()->_Db();
        DbManager::_()->_DbForWrite();
        DbManager::_()->_DbForRead();
        DbManager::DbCloseAll();
                
        //----------------
        $database_sinlge=[$database_list[0]];
        $options=[
            'database_list'=>$database_sinlge,
        ];
        DbManager::_(new DbManager())->init($options);
        $options['database_list']=[
        ];
        DbManager::_()->init($options,null);

        DbManager::Db();
        DbManager::DbForWrite();
        DbManager::DbForRead();
        DbManager::_()->init($options,null);
        DbManager::DbCloseAll();
        $options=[
            'db_before_get_object_handler'=>null,
            ];
    
        $options['database_list']=$database_list;
        
        DbManager::_()->init($options,null);
        DbManager::_()->_Db();
        
        ////
        $dn_options=[
            'log_sql'=>true,
        ];
        App::_(new App())->init($dn_options);
        $options=[
            'database_list'=>$database_list,
            'database_log_sql_query'=>true,
        ];
        
        DbManager::_(new DbManager())->init($options,App::_());
        $data=App::Db()->fetchColumn('select ?+? as t',1,2);
        DbManager::_()->options['database_log_sql_query']=false;
        $data=App::Db()->fetchColumn('select ?+? as t',1,2);

        
        var_dump($data);

        //
        echo "zzzzzzzzzzzzzzzzzzzzzzzzzz";
        $dn_options=[
        ];
        DbManager::_(new DbManager());
        App::_(new App())->init($dn_options);

        try{
            App::Db();
        }catch(\Exception $ex){
        }
        try{
            App::Db('zxvf');
        }catch(\Exception $ex){
        }

        $options=[
            'database_log_sql_query'=>true,
            'database' => null,
            'database_list' => null,
            'database_list_reload_by_setting'=>false,
        ];
        try{
            App::Db();
        }catch(\Exception $ex){
        }
        DbManager::_(new DbManager())->init($options,App::_());

        \LibCoverage\LibCoverage::End();

    }
    public static function beforeGet()
    {
        var_dump("OK");
    }
   
}
class MyDB extends Db
{
}
