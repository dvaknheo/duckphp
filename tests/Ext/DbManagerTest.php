<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\DbManager;
use DuckPhp\DB\DB;
use DuckPhp\DuckPhp as App;

class DbManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DbManager::class);
        $database_list= include \MyCodeCoverage::G()->options['path_data'] . 'database_list.php';
        
        $dn_options=[
            'skip_setting_file'=>true,
            'database_list'=>['zzz'],
        ];
        App::G()->init($dn_options);
        
        $options=[
            'database_list'=>$database_list,
        ];
        DbManager::G()->init($options,App::G());
        DbManager::G(new DbManager());
        
        $options=[

        'db_before_get_object_handler'=>[null,'beforeGet'],
        
        'database_list'=> $database_list,
        ];
        

        App::G()->extendComponents(static::class,['beforeGet'],[]);
        DbManager::G()->init($options,App::G());
        $options['database_list']=$database_list;
        DbManager::G()->init($options,null);
        
        DbManager::G()->setBeforeGetDBHandler(function(){var_dump("OK");});

        DbManager::G()->_Db();
        DbManager::G()->_DbForWrite();
        DbManager::G()->_DbForRead();
        DbManager::CloseAll();
                
        //----------------
        $database_sinlge=[$database_list[0]];
        $options=[
            'database_list'=>$database_sinlge,
        ];
        DbManager::G(new DbManager())->init($options);
        $options['database_list']=[
        ];
        DbManager::G()->init($options,null);

        DbManager::Db();
        DbManager::DbForWrite();
        DbManager::DbForRead();
        DbManager::G()->init($options,null);


                DbManager::CloseAll();

        $options=[

            'db_before_get_object_handler'=>null,
            ];
    
        $options['database_list']=$database_list;
        
        DbManager::G()->init($options,null);
        DbManager::G()->_Db();
        
        ////
        $dn_options=[
            'skip_setting_file'=>true,
            'log_sql'=>true,
        ];
        App::G(new App())->init($dn_options);
        $options=[
            'database_list'=>$database_list,
            'database_log_sql_query'=>true,
        ];
        
        DbManager::G(new DbManager())->init($options,App::G());
        $data=App::Db()->fetchColumn('select ?+? as t',1,2);
        DbManager::G()->options['database_log_sql_query']=false;
        $data=App::Db()->fetchColumn('select ?+? as t',1,2);

        
        var_dump($data);

        //
        echo "zzzzzzzzzzzzzzzzzzzzzzzzzz";
        $dn_options=[
            'skip_setting_file'=>true,
        ];
        DbManager::G(new DbManager());
        App::G(new App())->init($dn_options);

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
        DbManager::G(new DbManager())->init($options,App::G());

        \MyCodeCoverage::G()->end();

    }
    public static function beforeGet()
    {
        var_dump("OK");
    }
   
}
